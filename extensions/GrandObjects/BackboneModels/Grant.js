Grant = Backbone.Model.extend({

    initialize: function(){
        
    },

    urlRoot: 'index.php?action=api.grant',
    
    getGrantAward: function(){
        this.grantAward = new GrantAward();
        this.grantAward.set('id', this.get('grant_award_id'));
        this.grantAward.fetch();
        return this.grantAward;
    },
    
    defaults: function(){ return {
            id: null,
            user_id: 0,
            pi: {
                fullName: ""
            },
            copi: new Array(),
            copi_string: '',
            project_id: '',
            grant_award_id: 0,
            sponsor: '',
            external_pi: '',
            total: 0,
            portions: {},
            myportion: 0,
            adjusted_amount: 0,
            funds_before: 0,
            funds_after: 0,
            title: '',
            scientific_title: '',
            description: '',
            role: '',
            seq_no: '',
            prog_description: '',
            request: '',
            start_date: '',
            end_date: '',
            deleted: 0,
            url: '',
            contributions: null,
            exclude: false
        };
    }
    
});

Grants = Backbone.Collection.extend({
    model: Grant,
    
    url: 'index.php?action=api.grant'
});
