AdvancedSearch = Backbone.Model.extend({

    initialize: function() {
        
    },

    urlRoot: 'index.php?action=api.advancedsearch',
    
    defaults: {
        'title': "",
    }

});
