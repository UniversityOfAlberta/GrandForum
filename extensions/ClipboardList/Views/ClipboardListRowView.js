ClipboardListRowView = Backbone.View.extend({
    tagName: 'tr',
    parent: null,
    template: _.template($('#clipboardlist_row_template').html()),
    
    initialize: function(options){
	    this.parent = options.parent;
	    this.render();
    },

    events: {
    },

    render: function(){
        var mod = _.extend(this.model);
        this.el.innerHTML = this.template(mod);
        return this.$el;
    }
});
