LIMSCommentHistoryPmm = Backbone.View.extend({
    project: null,
    tagName: 'div',
    assignee: null,
    initialize: function(options) {
        this.project = options.project;
        this.assignee = options.assignee;
        this.selectTemplate();
        this.render();
    },

    selectTemplate: function() {
        this.template = _.template($('#lims_comment_history_template').html());
    },

    render: function() {
        var membersMap = {};
        this.project.members.each(function(member) {
            membersMap[member.get('id')] = member.get('fullName');
        });

        var assigneeId = this.assignee.get('id');
        var assigneeName = this.assignee.get('fullName');
        var allComments = this.model.get('commentsHistory');
        var assigneeComments = (allComments && allComments[assigneeId]) ? allComments[assigneeId] : [];

        this.$el.html(this.template({
            assigneeName: assigneeName,
            comments: assigneeComments,
            membersMap: membersMap
        }));
        return this.$el;
    }
});