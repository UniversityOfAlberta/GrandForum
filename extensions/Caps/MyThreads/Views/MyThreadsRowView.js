MyThreadsRowView = Backbone.View.extend({
    
    tagName: 'tr',
    parent: null,
    template: _.template($('#my_threads_row_template').html()),
    
    initialize: function(options){
        this.parent = options.parent;
        this.listenTo(this.model, "sync", this.render);
    },

    events: {
    },

    render: function(){
        var isMine = {"isMine": false};
	if(this.model.get('author').id == me.id){
             isMine.isMine = true;
	}
        var mod = _.extend(this.model.toJSON(), isMine);
        this.el.innerHTML = this.template(mod);
        return this.$el;
    }
});
