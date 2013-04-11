NewSearch = Backbone.Model.extend({

    initialize: function() {
        
    },

    urlRoot: 'index.php?action=api.newsearch',
    
    defaults: {
        'title': "",
    }

});
