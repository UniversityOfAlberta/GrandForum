LIMSStatusChangeViewPmm = Backbone.View.extend({
    tagName: 'div',
    project: null,
    
    events: {
        'click .deleteFile': 'deleteTaskFile', // Button to delete a file
        "click .view-comment-history": "showCommentHistory"
    },

    initialize: function(options) {
        this.project = options.project;
        this.isDialog = options.isDialog || false;
        this.listenTo(this.model, 'change:displayFiles', this.handleFileChange);
        this.listenTo(this.model, 'change:needsReviewerValidation', this.handleAssigneesOptions);
        this.listenTo(this.model, 'change:statusOptions', this.render);
        this.selectTemplate();
        this.handleAssigneesOptions();
        this.render();
    },

    handleFileChange: function() {
        var displayFiles = this.model.get('displayFiles') || {};
        var displayStatuses = _.clone(this.model.get('displayStatuses')) || {};
        var autoReviewIsOn = this.model.get('needsReviewerValidation');

        for (assigneeId in displayFiles) {
            var fileInfo = displayFiles[assigneeId];
            var changed = false;
            var fileWasAdded = fileInfo && !_.isEmpty(fileInfo.data) && !fileInfo.delete;

            if (displayStatuses[assigneeId] === 'Assigned' && fileWasAdded) {
                if (autoReviewIsOn) {
                    displayStatuses[assigneeId] = 'Done';
                } else {
                    displayStatuses[assigneeId] = 'Closed';
                }
                changed = true;
            }

            if (changed) {
                this.model.set('displayStatuses', displayStatuses, {silent: true});
                this.render();
            }
        }
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
    handleAssigneesOptions: function() {
        const needsReviewerValidation = this.model.get('needsReviewerValidation');

        const allStatusOptions = ['Assigned', 'Done', 'Closed'];

        
        const reviewerStatusOptions = ['Assigned', 'Done', 'Closed'];


        console.log(needsReviewerValidation)
        const isReviewNeeded = needsReviewerValidation === true ||
                               needsReviewerValidation === 1 ||
                               needsReviewerValidation === "1";
        const assigneeStatusOptions = isReviewNeeded
            ? ['Assigned', 'Done']
            : ['Assigned', 'Closed'];
        const statusOptions = {
            all: allStatusOptions,
            assignee: assigneeStatusOptions,
            reviewer: reviewerStatusOptions,
        };

        this.model.set("statusOptions", statusOptions)
    },
    render: function() {
        const data = this.model.toJSON();
        this.$el.html(this.template(data));
        return this.$el;
    },

    showCommentHistory: function(e) {
        LIMSPmmHelper.showCommentsHistory(e, this.model, this.project);
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
    }
});