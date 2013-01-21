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
        var left = this.model.get('left');
        var right = this.model.get('right');
        
        var leftCol = Array();
        var rightCol = Array();
        
        for(lId in left){
            leftCol.push("<span>" + left[lId] + "</span>");
        }
        for(rId in right){
            if(left.indexOf(right[rId]) == -1){
                rightCol.push("<span>" + right[rId] + "</span>");
            }
        }
        this.$('.left').html(leftCol.join());
        this.$('.right').html(rightCol.join());
        createSwitcheroos();
        return this.$el;
    }

});
