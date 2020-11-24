CRMContact = Backbone.Model.extend({

    initialize: function(){
        this.opportunities = new CRMOpportunities();
        this.opportunities.contact = this;
        this.on("sync", function(){ this.opportunities.fetch(); }.bind(this));
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
            url: "",
            isAllowedToEdit: true
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
