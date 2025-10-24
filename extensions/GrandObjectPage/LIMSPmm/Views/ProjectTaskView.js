ProjectTaskView = Backbone.View.extend({
    template: _.template($('#project_tasks_main_template').html()),
    dataTable: null,
    emailNotificationView: null,
    events: {
        'click #add-new-task-button': 'addNewTask'
    },
    
    initialize: function (options) {
        this.childViews = [];
        this.isEditMode = options.isEditMode;
        this.projectId = options.projectId;
        
        this.project = new Project({ id: this.projectId });
        this.tasks = new LIMSTasksPmm([], { projectId: this.projectId });
        
        var userRoles = _.pluck(_.filter(me.get('roles'), 
            function(el) {
                return el.title == this.project.get("name") 
                       ||  el.role !== PL
            }.bind(this)), 'role');
                       
        this.isManagement = _.intersection(userRoles, [PL, STAFF, MANAGER, ADMIN]).length > 0 ;

        this.listenTo(this.tasks, 'add', this.renderNewTaskRow);
        this.listenTo(this.tasks, 'change:toDelete', this.removeDeletedTaskView);
        this.listenTo(this.project, 'sync', this.render);
        this.listenTo(this.project.members, 'sync', this.render);
        this.listenTo(this.tasks, 'sync', this.render);
        
        this.project.fetch();
        this.project.getMembers();
        this.tasks.fetch();

        this.emailNotificationView = new LIMSEmailNotificationViewPmm({
            projectId: this.projectId,
            tasks: this.tasks
        });
        
        if (this.isEditMode) {
            this.setupFormHook();
        }
    },

    removeDeletedTaskView: function(model) {
        this.childViews = _.filter(this.childViews, function(view) {
            return view.model !== model;
        });
    },

    setupFormHook: function() {
        var self = this;
        $('form').on('submit', function(e) {
            if (this.submitted == 'Cancel') {
                return true;
            }
            if ($('button[value="Save Activities"]').is(':visible')) {
                e.preventDefault();
                $('button[value="Save Activities"]').prop('disabled', true);
                
                self.saveAllTasks().done(function() {
                    $('form').off('submit');
                    $('button[value="Save Activities"]').prop('disabled', false);
                    _.delay(function() {
                        $('button[value="Save Activities"]').click();
                    }, 10);
                }).fail(function(e) {
                    $('button[value="Save Activities"]').prop('disabled', false);
                    clearAllMessages();
                    addError(e.responseText || 'An error occurred while saving', true);
                });
            }
        });
    },

    addNewTask: function(e) {
        e.preventDefault();
        this.tasks.add(new LIMSTaskPmm({ projectId: this.projectId }));
    },
    
    renderNewTaskRow: function(taskModel) {
        var taskRowView = new TaskRowView({
            model: taskModel,
            project: this.project,
            isEditMode: this.isEditMode
        });
        this.childViews.push(taskRowView);
        this.$('#task-list').append(taskRowView.render().el);
    },
    
    render: function () {
        if (!this.project.id || !this.project.members) {
            return this;
        }

        if (this.dataTable) {
            this.dataTable.destroy();
            this.dataTable = null;
        }

        var templateData = {
            project: this.project.toJSON(),
            tasks: this.tasks.toJSON(),
            isEditMode: this.isEditMode,
            isManagement: this.isManagement
        };
        
        this.$el.html(this.template(templateData));
        
        this.emailNotificationView.setElement(this.$('#emailAccordion')).render();
                
        var $taskList = this.$('#task-list');
        _.each(this.childViews, function(child) { child.remove(); });

        this.childViews = [];
        this.tasks.each(function(taskModel) {
            if (!taskModel.toDelete) {
                this.renderNewTaskRow(taskModel);
            }
        }, this);

        this.$('#project-tasks-table').DataTable({
            iDisplayLength: 100
        });
        
        return this;
    },
    
    saveAllTasks: function() {
        var savePromises = [];
        
        this.tasks.each(function(taskModel) {
            taskModel.unset('displayAssignees');
            taskModel.unset('displayStatuses');
            taskModel.unset('displayFiles');
            taskModel.unset('displayReviewers');
            taskModel.unset('displayComments');
            
            taskModel.saving = true;
            
            if (!taskModel.toDelete) {
                if (taskModel.unsavedAttributes() !== false) {
                    savePromises.push(taskModel.save(null, {
                        success: function(){ taskModel.saving = false; },
                        error: function(){ taskModel.saving = false; }
                    }));
                } else {
                    taskModel.saving = false;
                }
            } else if (!taskModel.isNew()) {
                savePromises.push(taskModel.destroy({wait: true}));
            } else {
                taskModel.saving = false;
            }
        }, this);
        
        return $.when.apply(null, savePromises);
    },
    
    cleanup: function() {
        _.each(this.childViews, function(childView) {
            if (childView.remove) {
                childView.remove();
            }
        });
        this.childViews = [];
        $('form').off('submit');
    }
});