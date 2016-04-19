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

   url: function(){
        if(this.roles == undefined){
            return 'index.php?action=api.threads';
        }
        return 'index.php?action=api.threads/';
    }

});
