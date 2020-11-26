CRMTask = Backbone.Model.extend({

    initialize: function(){
    
    },

    urlRoot: function(){
        return 'index.php?action=api.crmtask';
    },

    defaults: function() {
        return{
            id: null,
            opportunity: "",
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
