Sop = Backbone.Model.extend({

    initialize: function(){
    },

    urlRoot: 'index.php?action=api.sop',

    defaults: function() {
        return{
            id: null,
            content: "",
            user: "",
            comments: new Array(),
            url: "",
            date_created: "0000-00-00 00:00:00",
        };
    }

});

Sops = Backbone.Collection.extend({

    model: Sop,
   
    search: '',

    url: function(){
        return 'index.php?action=api.sops/';
    }

});
