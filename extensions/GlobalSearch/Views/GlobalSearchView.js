GlobalSearchView = Backbone.View.extend({

    initialize: function(){
        this.template = _.template($("#global_search_template").html());
        Backbone.Subviews.add( this );
    },
    
    subviewCreators : {
        "globalSearchResults" : function() {
            return new GlobalSearchResultsView();
        }
    },
    
    events: {
        "keyup #globalSearchInput": "search"
    },
    
    search: function(e){
        var value = this.$el.find("#globalSearchInput").val();
        this.subviews.globalSearchResults.search(value);
    },

    render: function(){
        this.$el.html(this.template());
        var that = this;
        return this.$el;
    }

});

GlobalSearchResultsView = Backbone.View.extend({
    
    initialize: function(){
        this.template = _.template($("#global_search_results_template").html());
    },
    
    renderPeopleResults: function(value){
        var that = this;
        var g = new GlobalSearch({group: 'people', search: value});
        $.when(g.fetch()).then(function(){
            that.$el.find(".globalSearchResultsRows").empty();
            for(i in g.get('results')){
                if(i >= 5) break;
                that.$el.find(".globalSearchResultsRows").append(new SmallPersonCardView({model: new Person({id: g.get('results')[i]})}).render());
            }
        });
    },
    
    search: function(value){
        if(value.length > 0){
            this.$el.css('display', 'block');
            this.renderPeopleResults(value);
        }
        else{
            this.$el.css('display', 'none');
        }
    },
    
    render: function(){
        this.$el.html(this.template());
        this.$el.css('display', 'none');
        var that = this;
        $(document).click(function(e){
            if($("#globalSearchResults").has($(e.target)).length == 0){
                that.$el.css('display', 'none');
            }
        });
        return this.$el;
    }
    
});
