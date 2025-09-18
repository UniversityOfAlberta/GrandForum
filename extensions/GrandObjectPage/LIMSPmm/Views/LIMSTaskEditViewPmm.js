LIMSTaskEditViewPmm = Backbone.View.extend({

    tagName: "tr",

    project: null,

    editDialog: null,


    initialize: function(options){
        this.project = options.project;
        this.model.saving = false;
        this.listenTo(this.model, "sync", this.render);
        this.listenTo(this.model, "change:assignees", this.handleAssigneeChange);
        this.listenTo(this.model, "change:statuses", this.render);
        this.prepareDisplayState();
        this.selectTemplate();
        this.model.startTracking();
    },

    prepareDisplayState: function() {
        var primaryData = this.model.toJSON();
        var isEveryoneAssigned = _.some(primaryData.assignees, function(a) { return (a.id || a) == -1; });
        var displayAssignees = isEveryoneAssigned ? this.project.members.toJSON() : primaryData.assignees;

        var displayStatuses = {}, displayFiles = {}, displayReviewers = {}, displayComments = {};

        displayAssignees.forEach(function(assignee) {
            var assigneeId = assignee.id.toString();
            displayStatuses[assigneeId]  = primaryData.statuses[assigneeId]  || 'Assigned';
            displayFiles[assigneeId]     = _.clone(primaryData.files[assigneeId]) || {};
            displayReviewers[assigneeId] = primaryData.reviewers[assigneeId] || {};
            displayComments[assigneeId] = primaryData.comments[assigneeId] || '';
        }, this);

        var statusValues = Object.values(displayStatuses);
        var closedCount = statusValues.filter(function(s) { return s === 'Closed'; }).length;
        var pendingReviewCount = statusValues.filter(function(s) { return s === 'Done'; }).length;
        var accountedFor = closedCount + pendingReviewCount;
        var assignedCount = displayAssignees.length - accountedFor;

        var currentUserId = me.get('id');
        var originalAssignees = primaryData.assignees || [];
        var isCurrentUserAssignee = originalAssignees.some(function(assignee) {
            return (assignee.id || assignee) === currentUserId || (assignee.id || assignee) === -1;
        });
        var reviewerList = Object.values(displayReviewers);
        var isCurrentUserReviewer = reviewerList.some(function(reviewer) {
            return reviewer && reviewer.id === currentUserId;
        });

        this.model.set({
            displayAssignees: displayAssignees,
            displayStatuses: displayStatuses,
            displayFiles: displayFiles,
            displayReviewers: displayReviewers,
            displayComments: displayComments,
            displayClosedCount: closedCount,
            displayPendingReviewCount: pendingReviewCount,
            displayAssignedCount: assignedCount,
            isCurrentUserAssignee: isCurrentUserAssignee,
            isCurrentUserReviewer: isCurrentUserReviewer,
            isEveryoneAssigned: isEveryoneAssigned,
        });

    },
    
    selectTemplate: function(){
        // Get project role for current user
        var userRole = _.pluck(_.filter(me.get('roles'), function(el){return el.title == this.project.get("name") ||  el.role !== PL}.bind(this)), 'role');
        // Memebers can only change 'assigned' -> 'done'
        var isPLAllowed = _.intersection(userRole, [PL, STAFF, MANAGER, ADMIN]).length > 0 ;
        
        this.model.set('isLeaderAllowedToEdit', isPLAllowed);

        this.template = _.template($('#lims_task_edit_template').html());
    },
    
    events: {
        "click #deleteTask": "deleteTask",
        "click #changeStatusButton": "changeStatus",
        "change select[name=assignees]": "updateAssigneeOptions",
    },
    
    updateCounts: function() {
        this.$('.count-completed').text(this.model.get('displayClosedCount'));
        this.$('.count-pending').text(this.model.get('displayPendingReviewCount'));
        this.$('.count-assigned').text(this.model.get('displayAssignedCount'));
    },

    deleteTask: function(){
        this.model.toDelete = true;
        this.model.trigger("change:toDelete");
    },
    handleAssigneeChange: function(model, allAssignees) {
        var fullAssigneeObjects = _.map(allAssignees, function(item) {
            var assigneeId;
            if (_.isObject(item)) {
                if (item.name) {
                    return item;
                }
                assigneeId = item.id;
            } else {
                assigneeId = item;
            }
            if (assigneeId == -1) {
                return { id: -1, name: "Everyone", url: "" };
            }
            var userObject = this.project.members.get(assigneeId);
            return userObject ? {
                id: userObject.get('id'),
                name: userObject.get('fullName'),
                url: userObject.get('url') || ''
            } : { id: assigneeId, name: "Unknown", url: "" };
        }, this);
        this.model.set('assignees', fullAssigneeObjects, {silent: true});

        // Set 'Assigned' as a default status
        var currentStatuses = _.clone(this.model.get('statuses')) || {};

        fullAssigneeObjects.forEach(function(assignee) {
            var assigneeId = assignee.id.toString();
            if (!currentStatuses[assigneeId]) {
                currentStatuses[assigneeId] = 'Assigned';
            }
        });
        this.model.set('statuses', currentStatuses);

        var currentReviewers = _.clone(this.model.get('reviewers')) || {};
        var allMembers = this.project.members.toJSON();
        var updatedReviewers = ReviewerHelper.assignReviewersToNewUsers(allAssignees, currentReviewers, allMembers);
        this.model.set('reviewers', updatedReviewers);
        this.prepareDisplayState();
        this.updateCounts();
        this.updateAssigneeOptions();
    },

    updateAssigneeOptions: function(){
        var assigneesSelect = this.$('select[name=assignees]');
        var selectedAssignees = assigneesSelect.val() || [];
        if (selectedAssignees.includes('-1')) {
            assigneesSelect.find('option').not('[value="-1"]').prop('disabled', true);
            
            setTimeout(function() {
                var chosenContainer = assigneesSelect.next('.chosen-container');
                chosenContainer.find('.chosen-choices .search-choice').each(function() {
                    var choiceText = $(this).find('span').text().trim();
                    if (choiceText !== 'Everyone') {
                        $(this).hide();
                    }
                });
            }, 50);
        } else {
            assigneesSelect.find('option').prop('disabled', false);
            var chosenContainer = assigneesSelect.next('.chosen-container');
            chosenContainer.find('.chosen-choices .search-choice').show();
        }
        assigneesSelect.trigger("chosen:updated");

        assigneesSelect.hide();
    },

    changeStatus: function(){
        // Create a model for the status change dialog
        var view = new LIMSStatusChangeViewPmm({el: this.editDialog, model: this.model, isDialog: true, project: this.project});

        this.editDialog.view = view;
        $('body').append(this.editDialog);

        // Check if the dialog is already initialized
        if (this.editDialog.dialog('instance')) {
            this.editDialog.dialog('destroy');
        }
        
        $('body').append(this.editDialog);
        
        this.editDialog.dialog({
            height: $(window).height() * 0.75,
            width: 900,
            title: "Change Task Status",
            close: function(){
                if (view.closeDialog) {
                    view.closeDialog();
                }
            }
        });

        // Open the dialog
        this.editDialog.dialog('open');
    },

    renderTinyMCE: function(){
        _.defer(function(){
            this.$('textarea').tinymce({
                theme: 'modern',
                menubar: false,
                statusbar: false,
                relative_urls : false,
                convert_urls: false,
                plugins: 'link image charmap lists table paste',
                toolbar: [
                    'bold | link | bullist numlist'
                ],
                default_link_target: "_blank",
                rel_list: [
                    {title: 'No Referrer No Opener', value: 'noreferrer noopener'}
                ],
                paste_data_images: true,
                invalid_elements: 'h1, h2, h3, h4, h5, h6, h7, font',
                imagemanager_insert_template : '<img src="{$url}" width="{$custom.width}" height="{$custom.height}" />',
                setup: function(editor){
                    editor.on('change', function(e){
                        this.model.set('details',editor.getContent());
                    }.bind(this));
                }.bind(this)

            });
        }.bind(this));
    },

    isRowVisible: function() {
        var isAssignee = this.model.get('isCurrentUserAssignee');
        var isReviewer = this.model.get('isCurrentUserReviewer');
        var isLeader = this.model.get('isLeaderAllowedToEdit');
        return isAssignee || isReviewer || isLeader;
    },

    render: function(){
        this.prepareDisplayState();
        this.selectTemplate();

        if (this.isRowVisible()) {
            if(!this.model.saving){
                this.$el.html(this.template(this.model.toJSON()));
                _.defer(function(){
                    this.$('select[name=assignees]').show().chosen();
                    this.updateAssigneeOptions();
                }.bind(this));
            }
            this.editDialog = this.$('#changeStatusDialog');
            this.renderTinyMCE();
            this.$el.show();
        } else {
            this.$el.hide();
        }

        return this.$el;
    }

});
