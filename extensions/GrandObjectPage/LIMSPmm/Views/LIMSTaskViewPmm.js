LIMSTaskViewPmm = Backbone.View.extend({

    tagName: "tr",

    editDialog: null,


    initialize: function(){
        this.listenTo(this.model, "sync", this.render);
        this.template = _.template($('#lims_task_template').html());
        this.editDialog = $('<div></div>');
        this.prepareDisplayState()
    },

    events: {
        "click #checkStatus": "checkStatus"
    },

    prepareDisplayState: function() {
        var primaryData = this.model.toJSON();
        var currentUserId = me.get('id');

        var assignees = primaryData.assignees || [];
        var reviewersObject = primaryData.reviewers || {}; 
        var isCurrentUserAssignee = assignees.some(function(assignee) {
            return assignee.id === currentUserId;
        });

        var reviewerList = Object.values(reviewersObject);
        var isCurrentUserReviewer = reviewerList.some(function(reviewer) {
            return reviewer && reviewer.id === currentUserId;
        });

        var statuses = primaryData.statuses || {};
        var statusValues = Object.values(statuses);

        var closedCount = statusValues.filter(function(s) { return s === 'Closed'; }).length;
        var pendingReviewCount = statusValues.filter(function(s) { return s === 'Done'; }).length;
        
        var accountedFor = closedCount + pendingReviewCount;
        var assignedCount = statusValues.length - accountedFor;

        this.model.set({
            displayClosedCount: closedCount,
            displayPendingReviewCount: pendingReviewCount,
            displayAssignedCount: assignedCount,
            isCurrentUserAssignee: isCurrentUserAssignee,
            isCurrentUserReviewer: isCurrentUserReviewer
        });
    },

    checkStatus: function(){
        // Create a model for the status change dialog
        var view = new LIMSStatusCheckViewPmm({el: this.editDialog, model: this.model, isDialog: true});
        
        this.editDialog.view = view;
        $('body').append(this.editDialog);

        // Check if the dialog is already initialized
        if (this.editDialog.dialog('instance')) {
            this.editDialog.dialog('destroy');
        }
        
        $('body').append(this.editDialog);
        
        this.editDialog.dialog({
            height: $(window).height() * 0.75,
            width: 400,
            title: "Check Task Status"
        });

        // Open the dialog
        this.editDialog.dialog('open');
    },

    
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        return this.$el;
    }

});
