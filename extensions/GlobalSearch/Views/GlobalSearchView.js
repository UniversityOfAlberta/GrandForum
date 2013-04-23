GlobalSearchView = Backbone.View.extend({

    KEY_DOWN_ARROW: 40,
    KEY_UP_ARROW: 38,
    KEY_ENTER: 13,

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
        "keyup #globalSearchInput": "search",
        "keydown #globalSearchInput": "stopCaretMovement",
        "submit form#globalSearchForm": "submitForm"
    },
    
    stopCaretMovement: function(e){
        // Prevent the caret position from changing when pressing the up/down arrows
        if (e.keyCode == 38 || e.keyCode == 40){
            var pos = this.$("#globalSearchInput").selectionStart;
            this.$("#globalSearchInput").value = (e.keyCode == 38?1:-1)+parseInt(this.value,10);        
            this.$("#globalSearchInput").selectionStart = pos; this.$("#globalSearchInput").selectionEnd = pos;

            ignoreKey = true; setTimeout(function(){ignoreKey=false},1);
            e.preventDefault();
        }
    },
    
    submitForm: function(e){
        // Only submit if none of the results are currently selected by the keyboard
        if(this.subviews.globalSearchResults.searchIndex != -1){
            e.stopPropagation();
            return false;
        }
    },
    
    search: function(e){
        switch(e.keyCode){
            case 40: // DOWN
                this.subviews.globalSearchResults.shiftDown();
                break;
            case 38: // UP
                this.subviews.globalSearchResults.shiftUp();
                break;
            case 13: // ENTER
                this.subviews.globalSearchResults.click();
                break;
            default:
                var value = this.$el.find("#globalSearchInput").val();
                this.subviews.globalSearchResults.search(value);
                break;
        };
    },

    render: function(){
        this.$el.html(this.template());
        return this.$el;
    }

});

GlobalSearchResultsView = Backbone.View.extend({
    
    initialize: function(){
        this.template = _.template($("#global_search_results_template").html());
        this.searchIndex = -1;
        Backbone.Subviews.add(this);
    },
    
    events: {
        "resultsLoaded": "allResultsDone"
    },
    
    subviewCreators : {
        "personResults" : function(){
            return new PersonResultsView({model: new GlobalSearch({group: 'people', search: ''})});
        },
        "wikiResults" : function(){
            return new WikiResultsView({model: new GlobalSearch({group: 'wikipage', search: ''})});
        }
    },
    
    allResultsDone: function(){
        var noResults = true;
        for(sId in this.subviews){
            var subview = this.subviews[sId];
            if(subview.model.get('results').length > 0){
                noResults = false;
                break;
            }
        }
        if(noResults){
            this.$("#globalSearchResults").css('border-top-width', '0');
        }
        else{
            this.$("#globalSearchResults").css('border-top-width', '1px');
        }
    },
    
    click: function(){
        for(sId in this.subviews){
            var subview = this.subviews[sId];
            subview.click();
        }
    },
    
    shift: function(){
        var i = 0;
        for(sId in this.subviews){
            var subview = this.subviews[sId];
            subview.model.set('selected', this.searchIndex - i);
            i += subview.getResults().length;
        }
    },
    
    shiftDown: function(){
        var i = 0;
        for(sId in this.subviews){
            var subview = this.subviews[sId];
            i += subview.getResults().length;
        }
        this.searchIndex = Math.min(i-1, this.searchIndex + 1);
        this.shift();
    },
    
    shiftUp: function(){
        this.searchIndex = Math.max(-1, this.searchIndex - 1);
        this.shift();
    },
    
    search: function(value){
        if(value.length > 0){
            this.searchIndex = -1;
            this.$el.css('display', 'block');
            this.subviews.personResults.model.set('search', value);
            this.subviews.wikiResults.model.set('search', value);
            this.shift();
            var that = this;
            $.when(this.subviews.wikiResults.model.fetch(),
                   this.subviews.personResults.model.fetch())
            .then(function(){
                that.$el.trigger('resultsLoaded');
            });
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
        this.model.bind('change:selected', this.renderResults, this);
        this.template = _.template($("#global_search_group_template").html());
        this.value = '';
        this.cardsCache = Array();
        this.render();
    },
    
    click: function(){
        for(i in this.getResults()){
            if(i == this.model.get('selected')){
                window.location = this.cardsCache[this.model.get('results')[i]].$("a").attr('href');
            }
        }
    },
    
    getResults: function(){
        return this.model.get('results').slice(0, this.maxResults);
    },
    
    maxResults: 5, // Should be overridden if necessary
    
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
    
    maxResults: 5,
    
    renderResults: function(){
        this.$el.find(".globalSearchResultsRows").empty();
        var html = '';
        for(i in this.getResults()){
            var card = null;
            if(this.cardsCache[this.model.get('results')[i]] != undefined){
                card = this.cardsCache[this.model.get('results')[i]];
            }
            else{
                card = new SmallPersonCardView({model: new Person({id: this.model.get('results')[i]})});
                this.cardsCache[card.model.get('id')] = card;
                card.render();
            }
            if(i == this.model.get('selected')){
                card.$el.find(".small_card").addClass('small_card_hover');
            }
            else{
                card.$el.find(".small_card").removeClass('small_card_hover');
            }
            this.$el.find(".globalSearchResultsRows").append(card.$el);
        }
    },
    
    render: function(){
        this.$el.html(this.template({group: "People"}));
    }
});

WikiResultsView = ResultsView.extend({

    maxResults: 5,

    renderResults: function(){
        this.$el.find(".globalSearchResultsRows").empty();
        for(i in this.getResults()){
            var card = null;
            if(this.cardsCache[this.model.get('results')[i]] != undefined){
                card = this.cardsCache[this.model.get('results')[i]];
            }
            else{
                card = new SmallWikiCardView({model: new WikiPage({id: this.model.get('results')[i]})});
                this.cardsCache[card.model.get('id')] = card;
                card.render();
            }
            if(i == this.model.get('selected')){
                card.$el.find(".small_card").addClass('small_card_hover');
            }
            else{
                card.$el.find(".small_card").removeClass('small_card_hover');
            }
            this.$el.find(".globalSearchResultsRows").append(card.$el);
        }
    },
    
    render: function(){
        this.$el.html(this.template({group: "Wiki Pages"}));
    }
});
