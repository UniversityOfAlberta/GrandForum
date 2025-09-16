LIMSTaskPmm = Backbone.Model.extend({

    toDelete: false,
    opportunity: null, // Parent obj

    initialize: function(){
    
    },

    urlRoot: function(){
        return 'index.php?action=api.limstaskpmm';
    },

    defaults: function() {
        return{
            id: null,
            opportunity: "",
            assignees: [],
            task: "",
            taskType:"",
            dueDate: "",
            comments: {},
            details: "",
            statuses: {},
            isMemberAllowedToEdit: true,
            isLeaderAllowedToEdit: true,
            isAllowedToEdit: true,
            files: {},
            reviewers: {},
            commentsHistory: {}
        };
    }

});

/**
 * LIMSTasks Collection
 */
LIMSTasksPmm = Backbone.Collection.extend({
    model: LIMSTaskPmm,
    
    opportunity: null,
    
    url: function(){
        return 'index.php?action=api.limsopportunitypmm/' + this.opportunity.get('id') + '/tasks';
    }
});
