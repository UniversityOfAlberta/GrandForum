StoryComment = Backbone.Model.extend({

    initialize: function(){
    },

    urlRoot: 'index.php?action=api.storycomment/',

    defaults: function() {
        return{
            id: null,
            user_id: "",
	    parent_id: "",
	    story_id: "",
            author: "",
            message: "",
            date_created: "0000-00-00 00:00:00",
        };
    }
});

StoryComments = Backbone.Collection.extend({

   model: StoryComment,

   url: function(){
        if(this.roles == undefined){
            return 'index.php?action=api.storycomments';
        }
        return 'index.php?action=api.storycomments/';
    }

});
