Post = Backbone.Model.extend({

    initialize: function(){
    },

    urlRoot: 'index.php?action=api.post/',

    defaults: function() {
        return{
            id: null,
            user_id: "",
            author: "",
            message: "",
            date_created: "0000-00-00 00:00:00",
        };
    }
});

Posts = Backbone.Collection.extend({

   model: Post,

   url: function(){
        if(this.roles == undefined){
            return 'index.php?action=api.posts';
        }
        return 'index.php?action=api.posts/';
    }

});
