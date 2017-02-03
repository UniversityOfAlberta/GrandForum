PostView = Backbone.View.extend({
    
    parent: null,
    row: null,
    template: _.template($('#post_template').html()),
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
        var doDelete = false;
        if(wgLang == "en"){
            doDelete = confirm("Are you sure you want to delete this post?");
        }
        else{
            doDelete = confirm("Es-tu sur de vouloir supprimer cette annonce?");
        }
        if(doDelete){
            this.model.destroy({success: $.proxy(function(model, response){
                this.$el.remove();
            }, this)});
        }
    },

    submitPost: function(){
        this.model.save();
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
        if(this.model.get('author').id == me.id || _.intersection(_.pluck(me.get('roles'), 'role'), [STAFF,MANAGER,ADMIN]).length > 0){
             isMine.isMine = true;
        }
        var mod = _.extend(this.model.toJSON(), isMine);
        this.$el.html(this.template(mod));
        return this.$el;
    }
});
