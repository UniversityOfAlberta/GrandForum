NotesView = Backbone.View.extend({
    
    initialize: function() {
        this.template = _.template($('#notes_template').html());
    },
    
    events: {
    
    },

    render: function(){
        this.$el.html(this.template());
        return this.$el;
    }
});
