Contribution = Backbone.Model.extend({

    initialize: function(){
    
    },

    urlRoot: 'index.php?action=api.contribution',

    defaults: function() {
        return{
            id: null,
            name: "",
            start => "",
            end => "",
            partners => new Array(),
            cash => "",
            inkind => "",
            total => ""
        };
    }

});

Contributions = Backbone.Collection.extend({

    model: Contribution,

    url: function(){
        return 'index.php?action=api.contribution';
    }

});
