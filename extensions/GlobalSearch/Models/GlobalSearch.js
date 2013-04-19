GlobalSearch = Backbone.Model.extend({

    initialize: function(){
        
    },
    
    urlRoot: function(){
        return 'index.php?action=api.globalSearch/' + escape(this.get('group')) + '/' + escape(this.get('search')).replace(/\//g, ' ');
    },
    
    defaults: {
        id: '',
        search : '',
        group: '',
        results: []
    }

});
