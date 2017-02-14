GlobalSearch = Backbone.Model.extend({

    initialize: function(){
        
    },
    
    urlRoot: function(){
        return 'index.php?action=api.globalSearch/' + escape(this.get('group')) + '/' + encodeURIComponent(this.get('search')).replace(/\//g, ' ');
    },
    
    defaults: {
        id: '',
        search : '',
        group: '',
        selected: '',
        results: []
    }

});
