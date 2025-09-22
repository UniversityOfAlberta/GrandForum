// Helper functions for template views should go here
var LIMSPmmHelper = {
  assignReviewersToNewUsers: function(allAssignees, currentReviewers, allMembers) {
    var updatedReviewers = _.clone(currentReviewers) || {};
    var alreadyAssignedIds = _.values(updatedReviewers).map(r => r ? (r.id || r) : null).filter(Boolean);
    var preferredPool = _.reject(allMembers, member => _.contains(alreadyAssignedIds, member.id));

    allAssignees.forEach(function(assignee) {
      var assigneeId = assignee.id || assignee;
      if (assigneeId == -1) { return; }
      if (!_.isEmpty(updatedReviewers[assigneeId])) { return; }
      var availablePool = _.reject(preferredPool, member => member.id === assigneeId);
      if (availablePool.length === 0) {
        availablePool = _.reject(allMembers, member => member.id === assigneeId);
      }
      if (availablePool.length > 0) {
        var randomReviewer = _.sample(availablePool);
        updatedReviewers[assigneeId] = {
          id: randomReviewer.id,
          name: randomReviewer.fullName,
          url: randomReviewer.url || ''
        };
        preferredPool = _.reject(preferredPool, p => p.id === randomReviewer.id);
      }
    });
    return updatedReviewers;
  },

  showCommentsHistory: function(e, model, project) {
    e.preventDefault();
    
    var $button = $(e.currentTarget);
    var assigneeId = $button.data('assignee-id');
    var assigneeModel = project.members.get(assigneeId);
    
    var historyView = new LIMSCommentHistoryPmm({
        model: model,
        project: project,
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
  }
}