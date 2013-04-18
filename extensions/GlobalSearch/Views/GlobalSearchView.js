GlobalSearchView = Backbone.View.extend({

    initialize: function(){
        this.template = _.template($("#global_search_template").html());
    },
    
    events: {
        "keyup #globalSearchInput": "search"
    },
    
    search: function(e){
        var value = this.$el.find("#globalSearchInput").val();
    },

    render: function(){
        this.$el.html(this.template());
        return this.$el;
    }

});
