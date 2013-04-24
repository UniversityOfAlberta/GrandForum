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
        "keypress #globalSearchInput": "shift",
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
            case 37:
            case 39:
                break;
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
        "projectResults" : function(){
            return new ProjectResultsView({model: new GlobalSearch({group: 'projects', search: ''})});
        },
        "productResults" : function(){
            return new ProductResultsView({model: new GlobalSearch({group: 'products', search: ''})});
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
        $("#globalSearchThrobber > img").css('display', 'none');
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
            $("#globalSearchThrobber > img").css('display', 'block');
            this.searchIndex = -1;
            this.$el.css('display', 'block');
            var fetchPromises = Array();
            for(sId in this.subviews){
                var subview = this.subviews[sId];
                subview.model.set('search', value);
                fetchPromises.push(subview.model.fetch());
            }
            this.shift();
            var that = this;
            $.when.apply($, fetchPromises).then(function(){
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
    
    createCardView: function(model){
        console.error("Must implement 'createCardView'");
        return undefined;
    },
    
    createModel: function(){
        console.error("Must implement 'createModel'");
        return undefined;
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
    },
    
    renderResults: function(){
        this.$el.find(".globalSearchResultsRows").empty();
        var html = '';
        for(i in this.getResults()){
            var card = null;
            if(this.cardsCache[this.model.get('results')[i]] != undefined){
                card = this.cardsCache[this.model.get('results')[i]];
            }
            else{
                card = this.createCardView(this.createModel());
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
    }
});

PersonResultsView = ResultsView.extend({
    createCardView: function(model){
        return new SmallPersonCardView({model: model});
    },
    
    createModel: function(){
        return new Person({id: this.model.get('results')[i]});
    },
    
    render: function(){
        this.$el.html(this.template({group: "People"}));
    }
});

ProjectResultsView = ResultsView.extend({
    maxResults: 3,
    
    createCardView: function(model){
        return new SmallProjectCardView({model: model});
    },
    
    createModel: function(){
        return new Project({id: this.model.get('results')[i]});
    },
    
    render: function(){
        this.$el.html(this.template({group: "Projects"}));
    }
});

ProductResultsView = ResultsView.extend({
    maxResults: 5,
    
    createCardView: function(model){
        return new SmallProductCardView({model: model});
    },
    
    createModel: function(){
        return new Product({id: this.model.get('results')[i]});
    },
    
    render: function(){
        this.$el.html(this.template({group: "Products"}));
    }
});

WikiResultsView = ResultsView.extend({
    createCardView: function(model){
        return new SmallWikiCardView({model: model});
    },
    
    createModel: function(){
        return new WikiPage({id: this.model.get('results')[i]});
    },
    
    render: function(){
        this.$el.html(this.template({group: "Wiki Pages"}));
    }
});
