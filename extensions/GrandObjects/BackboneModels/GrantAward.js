GrantAward = Backbone.Model.extend({

    initialize: function(){
        this.grant = new Grant();
        
        this.bind("change:grant_id", function(){
            this.grant.set('id', this.get('grant_id'));
        });
    },

    urlRoot: 'index.php?action=api.grantaward',
    
    getGrant: function(){
        this.grant.fetch();
        return this.grant;
    },
    
    defaults: function(){ return {
            id: null,
            user_id: '',
            grant_id: 0,
            cle: '',
            department: '',
            organization: '',
            institution: '',
            province: '',
            country: '',
            fiscal_year: '',
            competition_year: '',
            amount: '',
            program_id: '',
            program_name: '',
            group: '',
            committee_code: '',
            committee_name: '',
            area_of_application_code: '',
            area_of_application_group: '',
            area_of_application: '',
            research_subject_code: '',
            research_subject_group: '',
            installment: '',
            partie: '',
            nb_partie: '',
            application_title: '',
            keyword: '',
            application_summary: '',
            coapplicants: '',
            partners: new Array()
        };
    }
    
});

GrantAwards = Backbone.Collection.extend({
    model: GrantAward,
    
    url: 'index.php?action=api.grantaward'
});
