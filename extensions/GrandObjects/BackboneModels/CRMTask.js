CRMTask = Backbone.Model.extend({

    toDelete: false,
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
            priority: "",
            status: "",
            isAllowedToEdit: true
        };
    }

});

CRMTask.priorityMap = {
    '': 'transparent',
    'High': '#ff6666',
    'Medium': '#ff8800',
    'Low': '#ffdd00'
};

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
