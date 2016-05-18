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
            title: "",
            posts: new Array(),
            url: "",
            date_created: "0000-00-00 00:00:00",
        };
    },
});

Threads = Backbone.Collection.extend({

    model: Thread,
   
    search: '',

    url: function(){
        if(this.search != ''){
            return 'index.php?action=api.threads/' + encodeURIComponent(this.search);
        }
        if(this.roles == undefined){
            return 'index.php?action=api.threads';
        }
        return 'index.php?action=api.threads/';
    }

});
