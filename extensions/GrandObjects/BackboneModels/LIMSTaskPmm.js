LIMSTaskPmm = Backbone.Model.extend({

    toDelete: false,

    initialize: function(){
    
    },

    urlRoot: function(){
        return 'index.php?action=api.limstaskpmm';
    },

    defaults: function() {
        return{
            id: null,
            projectId: "",
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
    projectId: null,
    initialize: function(models, options) {
        if (options && options.projectId) {
            this.projectId = options.projectId;
        }
    },

    url: function(){
    return '/index.php?action=api.limstaskpmm/project/' + this.projectId;
}

});
