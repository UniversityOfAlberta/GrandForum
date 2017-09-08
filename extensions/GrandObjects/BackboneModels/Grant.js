Grant = Backbone.Model.extend({

    initialize: function(){
        
    },

    urlRoot: 'index.php?action=api.grant',
    
    defaults: function(){ return {
            id: null,
            user_id: '',
            pi: null,
            copi: new Array(),
            copi_string: '',
            project_id: '',
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
