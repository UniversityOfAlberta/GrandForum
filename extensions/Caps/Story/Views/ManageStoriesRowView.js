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
        this.listenTo(this.model, "change", this.render);
    },

    events: {
        "change input[type=checkbox]": "toggleSelect",
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
