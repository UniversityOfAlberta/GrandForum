GrantPartner = Backbone.Model.extend({

    initialize: function(){
    
    },
    
    urlRoot: 'index.php?action=api.grantpartner',
    
    defaults: {
        id: null,
        award_id: '',
        part_institution: '',
        province: '',
        country: '',
        committee_name: '',
        fiscal_year: '',
        org_type: ''
    }

});

GrantPartners = Backbone.Collection.extend({
    
    model: GrantPartner 
    
});
