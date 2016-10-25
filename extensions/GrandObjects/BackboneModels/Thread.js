Thread = Backbone.Model.extend({

    initialize: function(){
    },

    urlRoot: 'index.php?action=api.thread',

    defaults: function() {
        return{
            id: null,
            user: "",
            users: "",
            author: "",
            authors: new Array(),
            roles: "",
            title: "",
            posts: new Array(),
            url: "",
            date_created: "0000-00-00 00:00:00",
        };
    },
    getPossibleRoles: function(){
        return _.pluck(me.get('roles'),'role');
    }

});

Threads = Backbone.Collection.extend({

    model: Thread,
    search: '',
    board_id: 0,

    url: function(){
        if(this.search != ''){
            return 'index.php?action=api.threads/' + this.board_id + "/" + encodeURIComponent(this.search);
        }
        return 'index.php?action=api.threads/' + this.board_id;
    }

});
