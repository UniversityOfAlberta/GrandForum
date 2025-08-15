// Helper functions for template views should go here
var ReviewerHelper = {
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
  }
}