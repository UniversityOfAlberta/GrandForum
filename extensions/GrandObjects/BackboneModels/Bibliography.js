Bibliography = Backbone.Model.extend({

    initialize: function(){
    },

    urlRoot: 'index.php?action=api.bibliography',

    defaults: function() {
        return{
            id: null,
            title: "",
            description: "",
            url: "",
            person: null,
            editors: null,
            products: null
        };
    }

});

Bibliographies = Backbone.Collection.extend({

    model: Bibliography,
    
    person: null,

    url: function(){
        if(this.person == null){
            return 'index.php?action=api.bibliography';
        }
        else{
            return 'index.php?action=api.bibliography/person/' + this.person;
        }
    }

});
