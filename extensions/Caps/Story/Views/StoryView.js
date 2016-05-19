StoryView = Backbone.View.extend({

    initialize: function(){
	//console.log(this.model);
        this.model.fetch({
            error: $.proxy(function(e){
                this.$el.html("This Story does not exist.");
            }, this)
        });
	this.model.bind('change', this.render, this);
        this.template = _.template($('#story_template').html());

    },

    events: {
        "click #editStory": "editStory",
	"click #deleteStory": "deleteStory",
    },

    deleteStory: function(){
	this.model.destroy();
    },

    editStory: function(){
//must add a  thing here so cannot hack
	if(me.id == this.model.get('author').id && this.model.get('approved') ==0){
            document.location = document.location + '/edit';
	}
	else{
	    return "";
	}
    },

    addRows: function(){
	var models = _.pluck(this.model.get('comments'), 'id');
	_.each(models, function(p){
	    var mod = new StoryComment({'id':p});
	    mod.fetch();
	    var row = new CommentView({model:mod, parent:this});
	    this.$("#commentRows").append(row.$el);
	});
	this.addNewRow();
   },

    addNewRow: function(){
        var newComment = new Comment({'story_id':this.model.id, 'user_id':me.id});
        var row = new CommentView({model: newComment, parent: this});
        this.$("#commentRows").append(row.$el);
   },

    renderAuthors: function(){
	if(_.findWhere(me.get('roles'), {"role":"Admin"}) == undefined && _.findWhere(me.get('roles'), {"role":"Manager"}) == undefined){
            if(me.id != this.model.get('author').id || this.model.get('approved') == 1){
                $("#deleteStory").remove();
                $("#editStory").remove();
	    }
	}
        var views = Array();
        var that = this;
	var author = this.model.get('author');
        var link = new Link({id: author.id,
                             text: author.name.replace(/&quot;/g, ''),
                             url: author.url,
                                 target: ''});
        views.push(new PersonLinkView({model: link}).render());
        var csv = new CSVView({el: this.$('#storyAuthor'), model: views});
        csv.separator = ', ';
        csv.render();
    },

    render: function(){
        main.set('title', striphtml(this.model.get('title')));
        this.$el.empty();
        var data = this.model.toJSON();
        this.$el.html(this.template(data));
        this.renderAuthors();
	this.addRows();
        return this.$el;
    }

});
