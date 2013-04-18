GlobalSearchView = Backbone.View.extend({

    initialize: function(){
        this.template = _.template($("#global_search_template").html());
    },

    render: function(){
        this.$el.html(this.template());
        return this.$el;
    }

});
