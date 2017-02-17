Bibliography = Backbone.Model.extend({

    initialize: function(){
    },

    urlRoot: 'index.php?action=api.bibliography',

    defaults: function() {
        return{
            id: null,
            title: "",
            description: "",
            person: null,
            products: null
        };
    }

});

Bibliographies = Backbone.Collection.extend({

    model: Bibliography,
    
    person: null,

    url: function(){
        return 'index.php?action=api.bibliography/person/' + this.person;
    }

});
