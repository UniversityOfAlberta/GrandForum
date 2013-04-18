GlobalSearchView = Backbone.View.extend({

    initialize: function(){
        this.template = _.template($("#global_search_template").html());
    },
    
    events: {
        "keyup #globalSearchInput": "search"
    },
    
    search: function(e){
        var value = this.$el.find("#globalSearchInput").val();
        this.$el.find("#globalSearchResults").css('display', 'block');
        this.searchResults.render();
    },

    render: function(){
        this.$el.html(this.template());
        var that = this;
        $(document).click(function(e){
            if(that.$el.find("#globalSearchResults").has($(e.target)).length == 0){
                that.$el.find("#globalSearchResults").css('display', 'none');
            }
        });
        this.$el.find("#globalSearchResults").css('display', 'none');
        this.searchResults = new GlobalSearchResultsView({el: "#globalSearchResults"});
        return this.$el;
    }

});

GlobalSearchResultsView = Backbone.View.extend({
    
    initialize: function(){
        this.template = _.template($("#global_search_results_template").html());
    },
    
    renderPeopleResults: function(){
        this.$el.find(".globalSearchResultsRows").empty();
        this.$el.find(".globalSearchResultsRows").append(new SmallPersonCardView({model: new Person({id: 3})}).render());
        this.$el.find(".globalSearchResultsRows").append(new SmallPersonCardView({model: new Person({id: 11})}).render());
        this.$el.find(".globalSearchResultsRows").append(new SmallPersonCardView({model: new Person({id: 159})}).render());
    },
    
    render: function(){
        this.$el.html(this.template());
        this.renderPeopleResults();
        return this.$el;
    }
    
});
