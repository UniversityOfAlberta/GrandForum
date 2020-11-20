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

/**
 * CRMOpportunities Collection
 */
CRMOpportunities = Backbone.Collection.extend({
    model: CRMOpportunity,
    
    contact: 0,
    
    url: function(){
        return 'index.php?action=api.crmcontact/' + this.contact + '/crmopportunities'
    }
});
