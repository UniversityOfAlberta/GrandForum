SwitcherooView = Backbone.View.extend({

    tagName: 'div',

    initialize: function(){
        this.model.bind('change', this.render);
        this.template = _.template($('#switcheroo_template').html());
    },
    
    switcheroo: function(){
        return $("#" + this.model.get('name'));
    },
    
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        createSwitcheroos();
        return this.$el;
    }

});
