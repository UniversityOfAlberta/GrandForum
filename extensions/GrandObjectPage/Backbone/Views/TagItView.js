TagItView = Backbone.View.extend({

    tagName: 'ul',

    initialize: function(){
        this.model.bind('change', this.render);
    },
    
    render: function(){
        this.$el.empty();
        this.$el.css('display', 'none');
        this.$el.tagit(this.model.get('options'));
        return this.$el;
    }

});
