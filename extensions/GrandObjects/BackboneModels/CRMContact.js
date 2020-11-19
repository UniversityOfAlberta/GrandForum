CRMContact = Backbone.Model.extend({

    initialize: function(){
    
    },

    urlRoot: function(){
        return 'index.php?action=api.crmcontact';
    },

    defaults: function() {
        return{
            id: null,
            owner: "",
            details: ""
        };
    }

});
