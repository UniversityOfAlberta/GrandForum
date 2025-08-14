// Helper functions for template views should go here
var ReviewerHelper = {
    assignReviewersToNewUsers: function(newlyAddedIds, currentReviewers, allMembers) {
    var updatedReviewers = _.clone(currentReviewers) || {};
    var alreadyAssignedIds = _.values(updatedReviewers).map(r => r ? (r.id || r) : null).filter(Boolean);
    var preferredPool = _.reject(allMembers, member => _.contains(alreadyAssignedIds, member.id));

    newlyAddedIds.forEach(function(assigneeId) {
      if (assigneeId === -1) { return; } // Skip 'Everyone'
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