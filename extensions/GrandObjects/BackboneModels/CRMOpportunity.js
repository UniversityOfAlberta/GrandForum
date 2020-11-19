CRMOpportunity = Backbone.Model.extend({

    initialize: function(){
    
    },

    urlRoot: function(){
        return 'index.php?action=api.crmopportunity';
    },

    defaults: function() {
        return{
            id: null,
            contact: "",
            category: ""
        };
    }

});
