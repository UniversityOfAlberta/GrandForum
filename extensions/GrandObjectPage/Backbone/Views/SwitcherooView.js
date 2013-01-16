SwitcherooView = Backbone.View.extend({

    tagName: 'div',

    initialize: function(){
        this.model.bind('change', this.render);
        this.template = _.template($('#switcheroo_template').html());
    },
    
    render: function(){
        this.$el.empty();
        this.$el.css('display', 'none');
        this.$el.html(this.template(this.model.toJSON()));
        
        var left = this.model.get('left');
        var right = this.model.get('right');
        
        for(lId in left){
            this.$('.left').append("<span>" + left[lId] + "</span>");
        }
        for(rId in right){
            if(left.indexOf(right[rId]) == -1){
                this.$('.right').append("<span>" + right[rId] + "</span>");
            }
        }
        createSwitcheroos();
        this.$el.slideDown(250);
        return this.$el;
    }

});
