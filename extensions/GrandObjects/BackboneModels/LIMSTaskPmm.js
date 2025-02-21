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
            assignee: {id: "",
                       name: "",
                       url: ""},
            task: "",
            dueDate: "",
            comments: "",
            status: "",
            isAllowedToEdit: true
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
