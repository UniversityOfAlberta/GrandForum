LIMSTask = Backbone.Model.extend({

    toDelete: false,
    opportunity: null, // Parent obj

    initialize: function(){
    
    },

    urlRoot: function(){
        return 'index.php?action=api.limstask';
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
            status: "",
            isAllowedToEdit: true
        };
    }

});

/**
 * LIMSTasks Collection
 */
LIMSTasks = Backbone.Collection.extend({
    model: LIMSTask,
    
    opportunity: null,
    
    url: function(){
        return 'index.php?action=api.limsopportunity/' + this.opportunity.get('id') + '/tasks';
    }
});
