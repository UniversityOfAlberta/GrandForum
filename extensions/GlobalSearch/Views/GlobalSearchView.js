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
        return this.$el;
    }

});

GlobalSearchResultsView = Backbone.View.extend({
    
    initialize: function(){
        this.template = _.template($("#global_search_results_template").html());
        Backbone.Subviews.add( this );
    },
    
    subviewCreators : {
        "personResults" : function() {
            return new PersonResultsView({model: new GlobalSearch({group: 'people', search: ''})});
        },
        "wikiResults" : function() {
            return new WikiResultsView({model: new GlobalSearch({group: 'wikipage', search: ''})});
        }
    },
    
    search: function(value){
        if(value.length > 0){
            this.$el.css('display', 'block');
            this.subviews.personResults.model.set('search', value);
            this.subviews.personResults.model.fetch();
            this.subviews.wikiResults.model.set('search', value);
            this.subviews.wikiResults.model.fetch();
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
            if($("#globalSearchResults").has($(e.target)).length == 0 && $(e.target).attr('id') != "globalSearchInput"){
                that.$el.css('display', 'none');
            }
        });
        return this.$el;
    }
    
});

ResultsView = Backbone.View.extend({
    initialize: function(){
        this.model.bind('sync', this.renderResultsPre, this);
        this.template = _.template($("#global_search_group_template").html());
        this.value = '';
        this.cardsCache = Array();
        this.render();
    },
    
    renderResultsPre: function(){
        if(this.model.get('results').length == 0){
            this.$el.empty();
        }
        else{
            if(this.$el.html() == ""){
                this.render();
            }
            this.renderResults();
        }
    }
});

PersonResultsView = ResultsView.extend({
    
    renderResults: function(){
        this.$el.find(".globalSearchResultsRows").empty();
        var html = '';
        for(i in this.model.get('results')){
            if(i >= 5) break;
            var card = null;
            if(this.cardsCache[this.model.get('results')[i]] != undefined){
                card = this.cardsCache[this.model.get('results')[i]];
            }
            else{
                card = new SmallPersonCardView({model: new Person({id: this.model.get('results')[i]})});
                this.cardsCache[card.model.get('id')] = card;
                card.render();
            }
            this.$el.find(".globalSearchResultsRows").append(card.$el);
        }
    },
    
    render: function(){
        this.$el.html(this.template({group: "People"}));
    }
});

WikiResultsView = ResultsView.extend({

    renderResults: function(){
        this.$el.find(".globalSearchResultsRows").empty();
        for(i in this.model.get('results')){
            if(i >= 5) break;
            var card = null;
            if(this.cardsCache[this.model.get('results')[i]] != undefined){
                card = this.cardsCache[this.model.get('results')[i]];
            }
            else{
                card = new SmallWikiCardView({model: new WikiPage({id: this.model.get('results')[i]})});
                this.cardsCache[card.model.get('id')] = card;
                card.render();
            }
            this.$el.find(".globalSearchResultsRows").append(card.$el);
        }
    },
    
    render: function(){
        this.$el.html(this.template({group: "Wiki Pages"}));
    }
});
