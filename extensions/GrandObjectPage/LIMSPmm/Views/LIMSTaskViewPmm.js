LIMSTaskViewPmm = Backbone.View.extend({
    project: null,

    tagName: "tr",

    editDialog: null,


    initialize: function(options){
        this.project = options.project;
        this.listenTo(this.model, "sync", this.render);
        this.selectTemplate();
        this.editDialog = $('<div></div>');
    },

    events: {
        "click #checkStatus": "checkStatus",
        "click .download-merged-csvs": "downloadMergedCsvs"
    },

    updateTaskSummary: function() {
        var primaryData = this.model.toJSON();
        var currentUserId = me.get('id');
        var originalAssignees = primaryData.assignees || [];
        
        var isEveryoneAssigned = originalAssignees.some(function(a) { return a.id === -1; });
        var effectiveAssignees;
        if (isEveryoneAssigned && this.project && this.project.members) {
            effectiveAssignees = this.project.members.toJSON();
        } else {
            effectiveAssignees = originalAssignees;
        }
        var statuses = primaryData.statuses || {};
        var effectiveStatusValues = effectiveAssignees.map(function(assignee) {
            return statuses[assignee.id] || 'Assigned';
        });
        var closedCount = effectiveStatusValues.filter(function(s) { return s === 'Closed'; }).length;
        var pendingReviewCount = effectiveStatusValues.filter(function(s) { return s === 'Done'; }).length;
        var accountedFor = closedCount + pendingReviewCount;
        var assignedCount = effectiveAssignees.length - accountedFor;

        var reviewersObject = primaryData.reviewers || {}; 
        var isCurrentUserAssignee = originalAssignees.some(function(assignee) {
            return assignee.id === currentUserId || assignee.id === -1;
        });

        var reviewerList = Object.values(reviewersObject);
        var isCurrentUserReviewer = reviewerList.some(function(reviewer) {
            return reviewer && reviewer.id === currentUserId;
        });

        this.model.set({
            displayClosedCount: closedCount,
            displayPendingReviewCount: pendingReviewCount,
            displayAssignedCount: assignedCount,
            isCurrentUserAssignee: isCurrentUserAssignee,
            isCurrentUserReviewer: isCurrentUserReviewer
        });
    },

    selectTemplate: function(){
        // Get project role for current user
        var userRole = _.pluck(_.filter(me.get('roles'), function(el){return el.title == this.project.get("name") ||  el.role !== PL}.bind(this)), 'role');
        // Memebers can only change 'assigned' -> 'done'
        var isPLAllowed = _.intersection(userRole, [PL, STAFF, MANAGER, ADMIN]).length > 0 ;
        
        this.model.set('isLeaderAllowedToEdit', isPLAllowed);

        this.template = _.template($('#lims_task_template').html());
    },

    checkStatus: function(){
        // Create a model for the status change dialog
        var view = new LIMSStatusCheckViewPmm({el: this.editDialog, model: this.model, isDialog: true, project: this.project});
        
        this.editDialog.view = view;
        $('body').append(this.editDialog);

        // Check if the dialog is already initialized
        if (this.editDialog.dialog('instance')) {
            this.editDialog.dialog('destroy');
        }
        
        $('body').append(this.editDialog);
        
        this.editDialog.dialog({
            height: $(window).height() * 0.75,
            width: 600,
            title: "Check Task Status"
        });

        // Open the dialog
        this.editDialog.dialog('open');
    },

    isRowVisible: function() {
        var isAssignee = this.model.get('isCurrentUserAssignee');
        var isReviewer = this.model.get('isCurrentUserReviewer');
        var isLeader = this.model.get('isLeaderAllowedToEdit');

        return isAssignee || isReviewer || isLeader;
    },

    downloadMergedCsvs: function(e) {
        e.preventDefault();

        if (!this.model || !this.model.id) {
            alert('Error: Task ID is not available.');
            return;
        }
        
        var taskId = this.model.id;
        var restPath = 'api.limstaskpmm/' + taskId + '/merge_csvs';
    
        var apiUrl = wgServer 
               + wgScriptPath 
               + '/index.php?action=' + restPath;

        $.ajax({
            url: apiUrl,
            method: 'GET',
            xhrFields: {
                responseType: 'blob'
            },
            success: function(data, textStatus, xhr) {
                var contentType = xhr.getResponseHeader('content-type');
                if (contentType && contentType.includes('text/csv')) {
                    var blob = new Blob([data], { type: 'text/csv' });
                    var url = window.URL.createObjectURL(blob);
                    var a = document.createElement('a');
                    a.href = url;
                    a.download = xhr.getResponseHeader('content-disposition')
                        .split('filename=')[1].replace(/"/g, '') || 'merged_data.csv';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    window.URL.revokeObjectURL(url);
                }
            },
            error: function(xhr, textStatus, errorThrown) {
                var errorMessage = 'An error occurred while downloading CSV files.';
                if (xhr.status === 404) {
                    errorMessage = 'No CSV files found for this task.';
                } else if (xhr.status === 400) {
                    errorMessage = 'Invalid task ID provided.';
                }
                alert( errorMessage);
            }.bind(this)
        });
    },

    render: function(){
        this.selectTemplate();
        this.updateTaskSummary()
        if (this.isRowVisible()) {
            var templateData = this.model.toJSON();
            if (this.project) {
                templateData.project = this.project.toJSON();
            }
            this.$el.html(this.template(templateData));
            this.$el.show();
        } else {
            this.$el.hide();
        }
        
        return this.$el;
    }

});
