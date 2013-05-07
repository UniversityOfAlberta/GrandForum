ProductLinkView = Backbone.View.extend({

    tagName: 'a',
    
    attributes: function(){
        return {'href': this.model.get('url'),
                'target': this.model.get('target'),
                'title': this.model.get('name')};
    },

    initialize: function(){
        this.model.bind('change', this.render, this);
    },
    
    render: function(){
        this.$el.empty();
        this.$el.html(this.model.get('text'));
        return this.el;
    }

});
