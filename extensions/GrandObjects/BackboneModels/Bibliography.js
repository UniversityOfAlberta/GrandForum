Bibliography = Backbone.Model.extend({

    initialize: function(){
    },

    urlRoot: 'index.php?action=api.bibliography',

    defaults: function() {
        return{
            id: null,
            person: null,
            products: null
        };
    }

});

Bibliographies = Backbone.Collection.extend({

    model: Bibliography,

    url: function(){
        return 'index.php?action=api.bibliographies';
    }

});
