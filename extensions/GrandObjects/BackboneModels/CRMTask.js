CRMTask = Backbone.Model.extend({

    opportunity: null, // Parent obj

    initialize: function(){
    
    },

    urlRoot: function(){
        return 'index.php?action=api.crmtask';
    },

    defaults: function() {
        return{
            id: null,
            opportunity: "",
            assignee: {id: "",
                       name: "",
                       url: ""},
            task: "",
            dueDate: "",
            transactions: new Array(),
            status: ""
        };
    }

});

/**
 * CRMTasks Collection
 */
CRMTasks = Backbone.Collection.extend({
    model: CRMTask,
    
    opportunity: null,
    
    url: function(){
        return 'index.php?action=api.crmopportunity/' + this.opportunity.get('id') + '/tasks';
    }
});
