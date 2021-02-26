LargeProjectCardView = Backbone.View.extend({

    lastWidth: 0,

    initialize: function(){
        this.model.bind('change', this.render, this);
        this.template = _.template($("#large_project_card_template").html());
    },

    render: function(options){
        this.$el.html(this.template(this.model.toJSON()));
        return this.$el;
    }

});
