CommentView = Backbone.View.extend({

    parent: null,
    row: null,
    template: _.template($('#comment_template').html()),
    isDialog: false,

    initialize: function(options){
        this.parent = options.parent;
        if(options.isDialog != undefined){
            this.isDialog = options.isDialog;
        }
        if(this.model.isNew()){
            this.render();
        }
        if(this.isDialog){
            $('#submitPost').remove();
        }
        this.listenTo(this.model, "sync", this.render);
    },

    events: {
        "click #submitPost": "submitPost",
    },

    submitPost: function(){
        this.model.save();
        this.parent.$("#commentRows").append(this.$el);
        this.parent.addNewRow();

    },

    render: function(){
        var classes = new Array();
        console.log(this.model);
        var isMine = {"isMine": false};
        if(this.model.get('author').id == me.id){
             isMine.isMine = true;
        }
        var mod = _.extend(this.model.toJSON(), isMine);
        this.el.innerHTML = this.template(mod);
        return this.$el;
    }
});
