LIMSTaskEditViewPmm = Backbone.View.extend({

    tagName: "tr",

    project: null,

    editDialog: null,


    initialize: function(options){
        this.project = options.project;
        this.model.saving = false;
        this.listenTo(this.model, "sync", this.render);
        this.listenTo(this.model, "change:assignees", this.handleAssigneeChange);
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
            displayComments[assigneeId]  = primaryData.comments[assigneeId]  || '';
        }, this);

        var statusValues = Object.values(displayStatuses);
        var closedCount = statusValues.filter(function(s) { return s === 'Closed'; }).length;
        var pendingReviewCount = statusValues.filter(function(s) { return s === 'Done'; }).length;
        var accountedFor = closedCount + pendingReviewCount;
        var assignedCount = displayAssignees.length - accountedFor;

        this.model.set({
            displayAssignees: displayAssignees,
            displayStatuses: displayStatuses,
            displayFiles: displayFiles,
            displayReviewers: displayReviewers,
            displayComments: displayComments,
            displayClosedCount: closedCount,
            displayPendingReviewCount: pendingReviewCount,
            displayAssignedCount: assignedCount
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
        "click #changeStatusButton": "changeStatus"
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
            width: 800,
            title: "Change Task Status"
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

    render: function(){
        // for (edId in tinyMCE.editors){
        //     var e = tinyMCE.editors[edId];
        //     if(e != undefined){
        //         e.destroy();
        //         e.remove();
        //     }
        // }
        this.selectTemplate();

        if(!this.model.saving){
            this.$el.html(this.template(this.model.toJSON()));
            _.defer(function(){
                this.$('select[name=assignees]').show().chosen();

            }.bind(this));
        }
        this.editDialog = this.$('#changeStatusDialog');


        this.renderTinyMCE();

        // if(!this.model.get('isAllowedToEdit')){
        //     this.$el.prepend('<td></td>');
        // }


        return this.$el;
    }

});
