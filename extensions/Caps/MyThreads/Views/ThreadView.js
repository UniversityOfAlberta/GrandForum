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
	document.location = "http://grand.cs.ualberta.ca/caps/index.php/Special:MyThreads";
    },

    editThread: function(){
        if(me.id == this.model.get('author').id || me.get('roles') == 'Admin'){
            document.location = document.location + '/edit';
        }
        else{
            return "";
        }
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
        if(_.findWhere(me.get('roles'), {"role":"Admin"}) == undefined &&  _.findWhere(me.get('roles'), {"role":"Manager"}) == undefined){
            $("#EditThreadButton").remove();
        }
        this.addRows();
        return this.$el;
    }

});
