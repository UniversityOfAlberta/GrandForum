ManageStoriesRowView = Backbone.View.extend({
    
    tagName: 'tr',
    parent: null,
    row: null,
    // Views
    editRoles: null,
    // Dialogs
    rolesDialog: null,
    template: _.template($('#manage_stories_row_template').html()),
    
    initialize: function(options){
        this.parent = options.parent;
        this.listenTo(this.model, "sync", this.render);
    },

    events: {
        "change input[type=checkbox]": "toggleSelect",
        "click .delete-icon": "deleteThread"
    },

    toggleSelect: function(e){
        this.setDirty(true);
    },

    setDirty: function(trigger){
        this.model.dirty = true;
        if(trigger){
            this.model.trigger("dirty");
        }
    },
    
    deleteThread: function(){
        var doDelete = false;
        if(wgLang == "en"){
            doDelete = confirm("Are you sure you want to delete this case?");
        }
        else if(wgLang == "fr"){
            doDelete = confirm("Voulez-vous vraiment supprimer ce cas?");
        }
        if(doDelete){
            this.model.destroy({success: $.proxy(function(model, response){
                this.$el.remove();
            }, this)});
        }
    },

    render: function(){
        var classes = new Array();
        var isMine = {"isMine": false};
	    if(this.model.get('author').id == me.id){
                 isMine.isMine = true;
	    }
        var mod = _.extend(this.model.toJSON(), isMine);
        var mod2 = _.extend(mod, me);
        this.el.innerHTML = this.template(mod2);
        this.$("td").each(function(i, val){
            classes.push($(val).attr("class"));
        });
        if(this.parent.table != null){
            var data = new Array();
            this.$("td").each(function(i, val){
                data.push($(val).htmlClean().html());
            });
            if(this.row != null){
                this.row.data(data);
            }
        }
        if(classes.length > 0){
            this.$("td").each(function(i, val){
                $(val).addClass(classes[i]);
            });
        } 
        return this.$el;
    }
});
