GrantAward = Backbone.Model.extend({

    initialize: function(){
        
    },

    urlRoot: 'index.php?action=api.grantaward',
    
    defaults: function(){ return {
            id: null,
            user_id: '',
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
            coapplicants: ''
        };
    }
    
});

GrantAwards = Backbone.Collection.extend({
    model: GrantAward,
    
    url: 'index.php?action=api.grantaward'
});
