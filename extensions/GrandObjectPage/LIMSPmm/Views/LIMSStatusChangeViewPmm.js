LIMSStatusChangeViewPmm = Backbone.View.extend({
    tagName: 'div',
    project: null,
    
    events: {
        'click #updateStatusButton': 'updateStatus', // Button to update status
        'click #cancelButton': 'closeDialog', // Button to cancel
        'click .deleteFile': 'deleteTaskFile' // Button to delete a file
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
        var fileId = $(e.currentTarget).data('assignee').toString();
        var files = this.model.get('files') || {};

        if (files[fileId]) {
            files[fileId].delete = true;
            files[fileId].data = '';
        }

        this.model.set('files', files);
        this.render();
    },

    render: function() {
        var templateData = this.model.toJSON();
        var allMembers = this.project.members;

        var cleanAssignees = _.map(templateData.assignees, function(assignee) {
            var assigneeId = assignee.id || assignee;
            var member = allMembers.get(assigneeId);
            if (member) {
                return { id: member.get('id'), name: member.get('fullName') };
            }
            return null;
        }).filter(Boolean);

        var peopleList = allMembers.map(function(member) {
            return { value: member.get('id'), option: member.get('fullName') };
        });
        peopleList.unshift({ value: '', option: 'None' });

        if (templateData.reviewers) {
            _.each(templateData.reviewers, function(reviewerId, assigneeId) {
                if (reviewerId && _.isObject(reviewerId)) {
                    reviewerId = reviewerId.id
                }
                if (reviewerId != null) {
                    templateData.reviewers[assigneeId] = reviewerId.toString();
                }
            });
        }
        templateData.assignees = cleanAssignees;
        templateData.peopleList = peopleList;
        
        this.$el.html(this.template(templateData)); 
        return this.$el;
    },

    updateStatus: function() {
        this.closeDialog();
    },

    closeDialog: function() {
        this.$el.dialog('close');
    }
});