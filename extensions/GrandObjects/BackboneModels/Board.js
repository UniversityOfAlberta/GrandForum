Board = Backbone.Model.extend({

    initialize: function(){
    },

    urlRoot: 'index.php?action=api.board',

    defaults: function() {
        return{
            id: null,
            title: "",
            description: "",
            url: "",
            nThreds: 0
        };
    }

});

Boards = Backbone.Collection.extend({

    model: Board,

    url: function(){
        return 'index.php?action=api.boards';
    }

});
