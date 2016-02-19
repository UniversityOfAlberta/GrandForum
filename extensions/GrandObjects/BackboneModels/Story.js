Story = Backbone.Model.extend({

    initialize: function(){
        this.author = new StoryAuthor();
	this.author.url = this.urlRoot + '/' + this.get('id') + '/author';    
    },

    urlRoot: 'index.php?action=api.story',

    getAuthor: function(){
	this.author.fetch();
	return this.author;
    },


    defaults: function() {
        return{
            id: null,
            rev_id: "",
	    user: "",
	    author: new Array(),
            title: "",
            story: "",
	    url: "",
            date_submitted: "0000-00-00 00:00:00",
            approved: 0,
	};
    }
});

Stories = Backbone.Collection.extend({
    
   model: Story,

   url: function(){
        if(this.roles == undefined){
            return 'index.php?action=api.stories';
        }
        return 'index.php?action=api.stories/';
    }
 
});

StoryAuthor = RelationModel.extend({
    initialize: function(){
    },
    
    urlRoot: function(){
	return 'index.php?action=api.story/' + this.get('productId') + '/author'
    },

    getOwner: function(){
	person = new Person({id: this.get('id')});
	person.fetch();
	return person;
    },

    idAttribute: 'id',

    defaults: {
	productId: null,
	id: null,
	personUrl: "",
	authorName: "",
    }
});
