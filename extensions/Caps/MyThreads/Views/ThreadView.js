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
        document.location = wgServer+wgScriptPath+"/index.php/Special:MyThreads#";
    },

    editThread: function(){
        if(me.id == this.model.get('author').id || _.findWhere(me.get('roles'), {"role":"Admin"}) != undefined ||  _.findWhere(me.get('roles'), {"role":"Manager"}) != undefined){
            document.location = document.location + '/edit';
        }
        else{
            return "";
        }
    },

    addRows: function(){
        this.$("#personRows").empty();
        this.$("#loading").html("<div id='loading'></div>");
        var spin = spinner("loading", 40, 75, 12, 10, '#888');
        this.$("#personRows").hide();
        var models = _.pluck(this.model.get('posts'), 'id');
        var ajax = new Array();
        _.each(models, function(p){
            var mod = new Post({'id':p});
            ajax.push(mod.fetch());
            var row = new PostView({model: mod, parent: this});
            this.$("#personRows").append(row.$el);
        });
        this.addNewRow();
        $.when.apply(undefined, ajax).then($.proxy(function(){
            this.$("#loading").empty();
            this.$("#personRows").show();
        }, this));
    },

    addNewRow: function(){
        var newPost = new Post({'thread_id':this.model.id, 'user_id':me.id});
        var row = new PostView({model: newPost, parent: this});
        this.$("#personRows").append(row.$el);
    },

    render: function(){
        main.set('title', striphtml(this.model.get('title')));
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
