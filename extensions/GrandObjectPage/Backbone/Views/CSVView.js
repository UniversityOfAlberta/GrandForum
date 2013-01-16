CSVView = Backbone.View.extend({

    tagName: 'span',

    initialize: function(){

    },
    
    separator: ', ',
    
    render: function(){
        this.$el.empty();
        _.each(this.model, function(model, index){
            if(index > 0){
                this.$el.append(this.separator);
            }
            this.$el.append(model);
        }, this);
        return this.$el;
    }

});
