ThreadView = Backbone.View.extend({
    template: _.template($('#thread_template').html()),

    initialize: function(){
        this.model.fetch({
            error: $.proxy(function(e){
                this.$el.html("This Thread does not exist.");
            }, this)
        });
	this.model.bind('sync', this.render);//change to on
    },

    events: {
        "click #EditThreadButton": "editThread",
	"click #BackButton": "back",
    },

    back: function(){
	document.location = "http://grand.cs.ualberta.ca/canet/index.php/Special:MyThreads";
    },

    editThread: function(){
            document.location = document.location + '/edit';
    },

    addRows: function(){
        if(this.table != undefined){
            order = this.table.order();
            searchStr = this.table.search();
            this.table.destroy();
            this.table = null;
        }
	var models = _.pluck(this.model.get('posts'), 'id');
	_.each(models, function(p){
	    var mod = new Post({'id':p});
	    mod.fetch();
  	    var row = new PostView({model: mod, parent: this});
            this.$("#personRows").append(row.$el);
	});
	this.addNewRow();
    },

    addNewRow: function(){
        var newPost = new Post({'thread_id':this.model.id, 'user_id':me.id});
        var row = new PostView({model: newPost, parent: this});
        this.$("#personRows").append(row.$el);
   },

    render: function(){
        main.set('title', this.model.get('title'));
        this.$el.empty();
        var data = this.model.toJSON();
        this.$el.html(this.template(data));
        this.addRows();
        return this.$el;
    }

});
