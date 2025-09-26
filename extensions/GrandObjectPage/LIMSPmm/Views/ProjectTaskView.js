ProjectTaskView = Backbone.View.extend({
    template: _.template($('#project_tasks_main_template').html()),

    events: {
        'click #add-new-task-button': 'addNewTask'
    },
    
    initialize: function (options) {
        this.childViews = [];
        this.isEditMode = options.isEditMode;
        this.projectId = options.projectId;
        this.project = new Project({ id: this.projectId });
        this.tasks = new LIMSTasksPmm([], { projectId: this.projectId });

        this.listenTo(this.tasks, 'add', this.renderNewTaskRow);
        
        $.when(this.project.fetch(), this.tasks.fetch()).done(this.render.bind(this));
        
        if (this.isEditMode) {
            this.setupFormHook();
        }
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
        var templateData = {
            project: this.project.toJSON(),
            tasks: this.tasks.toJSON(),
            isEditMode: this.isEditMode
        };
        
        this.$el.html(this.template(templateData));
        
        var $taskList = this.$('#task-list');
        _.each(this.childViews, function(child) { child.remove(); });

        this.childViews = [];
        this.tasks.each(function(taskModel) {
            this.renderNewTaskRow(taskModel);
        }, this);
        
        return this;
    },
    
    saveAllTasks: function() {
        var savePromises = [];
        
        _.each(this.childViews, function(childView) {
            if (childView.saveTask && typeof childView.saveTask === 'function') {
                var savePromise = childView.saveTask();
                if (savePromise && savePromise.then) {
                    savePromises.push(savePromise);
                }
            }
        });
        
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