ThreadView = Backbone.View.extend({
    template: _.template($('#thread_template').html()),
    isComment: false,
    tinyMCEMention: null,

    initialize: function(options){
        if(options.isComment != undefined){
            this.isComment = options.isComment;
        }
        if(options.tinyMCEMention != undefined){
            this.tinyMCEMention = options.tinyMCEMention;
        }
        this.model.fetch({
            error: $.proxy(function(e){
                this.$el.html("This Thread does not exist.");
            }, this)
        });
        this.listenTo(this.model, 'sync', this.render);
    },

    events: {
        "click #EditThreadButton": "editThread",
    },

    editThread: function(){
        document.location = document.location + '/edit';
    },

    addRows: function(){
        this.$("#postRows").empty();
        this.$("#loading").html("<div id='loading'></div>");
        var spin = spinner("loading", 40, 75, 12, 10, '#888');
        this.$("#postRows").hide();
        var models = _.pluck(this.model.get('posts'), 'id');
        var ajax = new Array();
        _.each(models, $.proxy(function(p){
            var mod = new Post({'id':p});
            ajax.push(mod.fetch());
            var row = new PostView({model: mod, parent: this, isComment: this.isComment, tinyMCEMention: this.tinyMCEMention});
            this.$("#postRows").append(row.$el);
        }, this));
        this.addNewRow();
        $.when.apply(undefined, ajax).then($.proxy(function(){
            this.$("#loading").empty();
            this.$("#postRows").show();
        }, this));
    },

    addNewRow: function(){
        var newPost = new Post({'thread_id':this.model.id, 'user_id':me.id});
        var row = new PostView({model: newPost, parent: this, isComment: this.isComment, tinyMCEMention: this.tinyMCEMention});
        this.$("#postRows").append(row.$el);
    },

    render: function(){
        if(this.isComment && this.model.get('title') == ''){
            // This is a comment, dont do an extra render
            return this.$el;
        }
        if (!this.isComment){
            main.set('title', "<a href='" + wgServer + wgScriptPath + "/index.php/Special:MyThreads'>Message Boards</a> &gt; " + 
                          "<a href='" + this.model.get('board').url + "'>" + this.model.get('board').title + "</a> &gt; " + 
                          striphtml(this.model.get('title')));
        }
        this.$el.empty();
        var data = this.model.toJSON();
        this.$el.html(this.template(data));
        this.addRows();
        return this.$el;
    }

});
