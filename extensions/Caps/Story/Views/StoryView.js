StoryView = Backbone.View.extend({

    initialize: function(){

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
	    if(_.findWhere(me.get('roles'), {"role":"Admin"}) == undefined && _.findWhere(me.get('roles'), {"role":"Manager"}) == undefined){
                if(me.id != this.model.get('author').id || this.model.get('approved') == 1){
                    $("#deleteStory").remove();
                    $("#editStory").remove();
	        }
	    }
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
        var newComment = new StoryComment({'story_id':this.model.id, 'user_id':me.id});
        var row = new CommentView({model: newComment, parent: this});
        this.$("#commentRows").append(row.$el);
   },

    render: function(){
        main.set('title', striphtml(this.model.get('title')));
        this.$el.empty();
        console.log(this.model);
        var data = this.model.toJSON();
        this.$el.html(this.template(data));
	    this.addRows();
        return this.$el;
    }

});
