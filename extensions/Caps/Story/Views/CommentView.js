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
        "click .edit-icon": "editPost",
        "click .delete-icon": "deletePost",
        "click #submitPost": "submitPost",
        "click #cancel": "cancel",
        "click #save": "save",
    },
    
    editPost: function(){
        this.oldMessage = this.model.get('message');
        this.editing = true;
        this.render();
    },
    
    deletePost: function(){
        this.model.destroy({success: $.proxy(function(model, response){
            this.$el.remove();
        }, this)});
    },

    submitPost: function(){
        this.model.save();
        this.parent.$("#commentRows").append(this.$el);
        this.parent.addNewRow();
    },
    
    cancel: function(){
        this.editing = false;
        this.model.set('message', this.oldMessage);
        this.render();
    },
    
    save: function(){
        this.editing = false;
        this.model.save();
    },

    render: function(){
        var classes = new Array();
        var isMine = {"isMine": false};
        if(this.model.get('author').id == me.id){
             isMine.isMine = true;
        }
        var mod = _.extend(this.model.toJSON(), isMine);
        this.el.innerHTML = this.template(mod);
        return this.$el;
    }
});
