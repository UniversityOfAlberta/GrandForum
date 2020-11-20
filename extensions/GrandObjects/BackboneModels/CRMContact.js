CRMContact = Backbone.Model.extend({

    initialize: function(){
    
    },

    urlRoot: function(){
        return 'index.php?action=api.crmcontact';
    },

    defaults: function() {
        return{
            id: null,
            title: "",
            owner: "",
            details: "",
            url: ""
        };
    }

});

/**
 * CRMContacts Collection
 */
CRMContacts = Backbone.Collection.extend({
    model: CRMContact,
    
    url: function(){
        return 'index.php?action=api.crmcontact';
    }
});
