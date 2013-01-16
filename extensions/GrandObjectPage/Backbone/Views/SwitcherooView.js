SwitcherooView = Backbone.View.extend({

    tagName: 'div',

    initialize: function(){
        this.model.bind('change', this.render);
        this.template = _.template($('#switcheroo_template').html());
    },
    
    separator: ', ',
    
    render: function(){
        this.$el.empty();
        
        var left = this.model.get('left');
        var right = this.model.get('right');
        
        
        _.each(this.model, function(model, index){
            if(index > 0){
                this.$el.append(this.separator);
            }
            this.$el.append(model);
        }, this);
        return this.el;
    }

});
