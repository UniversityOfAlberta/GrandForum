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
    
    render: function(){
        var classes = new Array();
        this.$("td").each(function(i, val){
            classes.push($(val).attr("class"));
        });
        this.el.innerHTML = this.template(this.model.toJSON());
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
