ExpertRowView = Backbone.View.extend({
    tagName: 'tr',
    parent: null,
    template: _.template($('#expert_row_template').html()),
    
    initialize: function(options){
	    this.parent = options.parent;
            this.listenTo(this.model, "sync", this.render);
    },

    events: {
    },

    render: function(){
	var i = this.model.toJSON();
        var mod = _.extend(this.model.toJSON());
        this.el.innerHTML = this.template(mod);
        return this.$el;
    }
});
