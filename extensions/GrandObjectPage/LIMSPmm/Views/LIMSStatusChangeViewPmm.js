LIMSStatusChangeViewPmm = Backbone.View.extend({
    tagName: 'div',
    project: null,
    
    events: {
        'click #cancelButton': 'closeDialog', // Button to cancel
        'click .deleteFile': 'deleteTaskFile', // Button to delete a file
        "click .view-comment-history": "showCommentHistory"
    },

    initialize: function(options) {
        this.project = options.project;
        this.isDialog = options.isDialog || false;
        this.selectTemplate();
        this.render();
        
        
    },

    selectTemplate: function(){

        
        // Get project role for current user
        var userRole = _.pluck(_.filter(me.get('roles'), function(el){ 
            return el.title == this.project.get("name") ||  el.role !== PL; 
        }.bind(this)), 'role');
    
        var isPLAllowed = _.intersection(userRole, [PL, STAFF, MANAGER, ADMIN]).length > 0;
    
        var isMemberAllowed = !isPLAllowed && (me.get);
    
        this.model.set('isLeaderAllowedToEdit', isPLAllowed);
        this.model.set('isMemberAllowedToEdit', isMemberAllowed);
        
    
        this.model.set('isEditableStatus', isPLAllowed || isMemberAllowed);
    
        this.template = _.template($(
            '#lims_status_change_template').html());
    },


    deleteTaskFile: function(e){
        var assigneeId = $(e.currentTarget).data('assignee').toString();
        var displayFiles = this.model.get('displayFiles') || {};
        if (displayFiles[assigneeId]) {
            displayFiles[assigneeId].delete = true;
            displayFiles[assigneeId].data = '';
        }

        this.model.set('displayFiles', displayFiles);
        this.render();
    },

    render: function() {
        this.$el.html(this.template(this.model.toJSON())); 
        return this.$el;
    },

    showCommentHistory: function(e) {
        e.preventDefault();

        var $button = $(e.currentTarget);
        var assigneeId= $button.data('assignee-id');
        var assigneeModel = this.project.members.get(assigneeId);
        var historyView = new LIMSCommentHistoryPmm({
            model: this.model,
            project: this.project,
            assignee: assigneeModel
        });

        var $dialog = $('<div>').append(historyView.el);

        $dialog.dialog({
            title: "Comment History for " + assigneeModel.get('fullName'),
            modal: true,
            width: 400,
            close: function() {
                historyView.remove();
                $(this).dialog('destroy').remove();
            }
        });
    },

    closeDialog: function() {
        var final = {};
        var data = this.model.toJSON();
        var finalAssignees = _.clone(data.assignees);

        for (var assigneeId in data.displayStatuses) {
            var wasExplicitlyAssigned = _.some(data.assignees, function(a) { return (a.id || a).toString() === assigneeId; });

            var statusChanged = data.displayStatuses[assigneeId] !== (data.statuses[assigneeId] || 'Assigned');
            var fileChanged = (data.displayFiles[assigneeId] && !_.isEmpty(data.displayFiles[assigneeId].data)) || (data.displayFiles[assigneeId] && data.displayFiles[assigneeId].delete);
            var reviewerChanged = data.displayReviewers[assigneeId] && data.displayReviewers[assigneeId].id && !_.isEqual(data.displayReviewers[assigneeId], data.reviewers[assigneeId]);
            var commentAdded = data.displayComments[assigneeId] && data.displayComments[assigneeId] !== '';

            var hasMeaningfulChange = statusChanged || fileChanged || reviewerChanged || commentAdded;
            if (hasMeaningfulChange && !wasExplicitlyAssigned) {
                finalAssignees.push({id: assigneeId});
            }

            if (wasExplicitlyAssigned || hasMeaningfulChange) {
                if (!final.statuses) final.statuses = {};
                final.statuses[assigneeId] = data.displayStatuses[assigneeId];
                if (!final.files) final.files = {};
                final.files[assigneeId] = data.displayFiles[assigneeId];
                if (!final.reviewers) final.reviewers = {};
                final.reviewers[assigneeId] = data.displayReviewers[assigneeId];
                if (!final.comments) final.comments = {};
                final.comments[assigneeId] = data.displayComments[assigneeId];
            }
        }
        final.assignees = finalAssignees;
        // as assignes is modified, handleAssigneeChange will be called
        this.model.set(final);
        this.$el.dialog('close');
    }
});