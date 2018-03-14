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
            total: 0,
            funds_before: 0,
            funds_after: 0,
            title: '',
            description: '',
            role: '',
            seq_no: '',
            prog_description: '',
            request: '',
            start_date: '',
            end_date: '',
            url: '',
            contributions: null
        };
    }
    
});

Grants = Backbone.Collection.extend({
    model: Grant,
    
    url: 'index.php?action=api.grant'
});
