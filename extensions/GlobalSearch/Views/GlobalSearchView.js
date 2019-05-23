GlobalSearchView = Backbone.View.extend({

    KEY_DOWN_ARROW: 40,
    KEY_UP_ARROW: 38,
    KEY_ENTER: 13,

    initialize: function(){
        this.template = _.template($("#global_search_template").html());
        Backbone.Subviews.add(this);
    },
    
    subviewCreators: {
        "globalSearchResults" : function() {
            return new GlobalSearchResultsView();
        }
    },
    
    events: {
        "keyup #globalSearchInput": "search",
        "keydown #globalSearchInput": "shift",
        "submit form#globalSearchForm": "submitForm"
    },
    
    submitForm: function(e){
        // Only submit if none of the results are currently selected by the keyboard
        if(this.subviews.globalSearchResults.searchIndex != -1){
            e.stopPropagation();
            return false;
        }
    },
    
    shift: function(e){
        // Prevent the caret position from changing when pressing the up/down arrows
        if (e.keyCode == 38 || e.keyCode == 40){
            var pos = this.$("#globalSearchInput").selectionStart;
            this.$("#globalSearchInput").value = (e.keyCode == 38?1:-1)+parseInt(this.value,10);        
            this.$("#globalSearchInput").selectionStart = pos; this.$("#globalSearchInput").selectionEnd = pos;

            ignoreKey = true; setTimeout(function(){ignoreKey=false},1);
            e.preventDefault();
        }
        switch(e.keyCode){
            case 40: // DOWN
                if(this.subviews.globalSearchResults.$el.css('display') == 'none' &&
                   this.$el.find("#globalSearchInput").val() != ''){
                    this.subviews.globalSearchResults.searchIndex = -1;
                    this.subviews.globalSearchResults.$el.css('display', 'block');
                }
                this.subviews.globalSearchResults.shiftDown();
                break;
            case 38: // UP
                this.subviews.globalSearchResults.shiftUp();
                break;
            case 13: // ENTER
                this.subviews.globalSearchResults.click();
                break;
        };
    },
    
    search: function(e){
        switch(e.keyCode){
            case 37:
            case 38:
            case 39:
            case 40:
            case 13:
            case 16:
                break;
            default:
                var value = this.$("#globalSearchInput").val();
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
        this.fetchPromises = Array();
        Backbone.Subviews.add(this);
        $(window).resize(function(){
            this.$("#globalSearchResults").css("max-height", ($(window).height() - this.$("#globalSearchResults").offset().top - 25) + "px");
        });
    },
    
    events: {
        "resultsLoaded": "allResultsDone"
    },
    
    subviewCreators : {
        "personResults" : function(){
            return new PersonResultsView({parent: this, model: new GlobalSearch({group: 'people', search: ''})});
        },
        "expertResults" : function(){
            return new ExpertResultsView({parent: this, model: new GlobalSearch({group: 'experts', search: ''})});
        },
        "projectResults" : function(){
            return new ProjectResultsView({parent: this, model: new GlobalSearch({group: 'projects', search: ''})});
        },
        "productResults" : function(){
            return new ProductResultsView({parent: this, model: new GlobalSearch({group: 'products', search: ''})});
        },
        "bibliographyResults" : function(){
            return new BibliographyResultsView({parent: this, model: new GlobalSearch({group: 'bibliographies', search: ''})});
        },
        "wikiResults" : function(){
            return new WikiResultsView({parent: this, model: new GlobalSearch({group: 'wikipage', search: ''})});
        },
        "pdfResults" : function(){
            return new PDFResultsView({parent: this, model: new GlobalSearch({group: 'pdf', search: ''})});
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
        if(skin == 'cavendish'){
            if(noResults){
                this.$("#globalSearchResults").css('border-top-width', '0');
            }
            else{
                this.$("#globalSearchResults").css('border-top-width', '1px');
            }
        }
        $("#globalSearchThrobber > .throbber").css('display', 'none');
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
            if(subview.$(".globalSearchResultsMoreRows").hasClass("showing")){
                i += subview.getResults().length;
            }
            else{
                i += Math.min(subview.getResults().length, subview.maxResults);
            }
        }
    },
    
    shiftDown: function(){
        var i = 0;
        for(sId in this.subviews){
            var subview = this.subviews[sId];
            if(subview.$(".globalSearchResultsMoreRows").hasClass("showing")){
                i += subview.getResults().length;
            }
            else{
                i += Math.min(subview.getResults().length, subview.maxResults);
            }
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
            for(fId in this.fetchPromises){
                try{
                    this.fetchPromises[fId].abort();
                }
                catch(e){
                
                }
            }
            this.fetchPromises = Array();
            $("#globalSearchThrobber > .throbber").css('display', 'block');
            this.searchIndex = -1;
            this.$el.css('display', 'block');
            for(sId in this.subviews){
                var subview = this.subviews[sId];
                subview.model.set('search', value);
                this.fetchPromises.push(subview.model.fetch());
            }
            this.shift();
            var that = this;
            $.when.apply($, this.fetchPromises).then(function(){
                that.$el.trigger('resultsLoaded');
            });
        }
        else{
            this.$el.css('display', 'none');
        }
    },
    
    render: function(){
        this.$el.html(this.template());
        this.$("#globalSearchResults").css("max-height", ($(window).height() - this.$("#globalSearchResults").offset().top - 25) + "px");
        this.$el.css('display', 'none');
        if(skin == 'cavendish2'){
            if($("#globalSearchInput").is(":visible")){
                setInterval(function(){
                    if(!$("#globalSearchResults").is(":animated")){
                        if($("#globalSearchResults").height() > 0){
                            $("#globalSearchInput").animate({borderBottomLeftRadius: 0}, 50);
                        }
                        else{
                            $("#globalSearchInput").animate({borderBottomLeftRadius: 10}, 50);
                        }
                    }
                    var length = $("#globalSearchResults > div:visible > div").length;
                    $("#globalSearchResults > div:visible > div").each(function(i, el){
                        if(i == length - 1){
                            $(el).css('border-bottom-width', 0);
                        }
                        else{
                            $(el).css('border-bottom-width', 1);
                        }
                    });
                }, 100);
            }
        }
        $(document).click(function(e){
            if($("#globalSearchResults").has($(e.target)).length == 0 && $(e.target).attr('id') != "globalSearchInput"){
                this.$el.css('display', 'none');
            }
        }.bind(this));
        if(typeof pageRouter != 'undefined'){
            // In the event clicking the result only changes the router page
            pageRouter.bind('all', function(event){
                this.$el.css('display', 'none');
                $("#globalSearchInput").val("");
            }.bind(this));
        }
        return this.$el;
    }
    
});

ResultsView = Backbone.View.extend({
    
    parent: null,

    initialize: function(options){
        this.parent = options.parent;
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
    
    createModel: function(obj){
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
        var results = this.model.get('results');
        return results.slice(0, Math.min(results.length, this.absoluteMaxResults));
    },
    
    absoluteMaxResults: 10,
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
    
    toggleMoreResults: function(){
        this.$(".globalSearchResultsMoreRows").toggleClass('showing');
        var results = this.getResults();
        var extra = results.length - this.maxResults;
        if(this.$(".globalSearchResultsMoreRows").hasClass("showing")){
            this.$("#showMoreResults").text("Show fewer results");
            this.$(".globalSearchResultsMoreRows").show();
        }
        else{
            this.$("#showMoreResults").text("Show " + this.$("#showMoreResults").attr('value') + " more results");
            this.$(".globalSearchResultsMoreRows").slideUp();
        }
        for(i in results){
            var card = null;
            if(!_.isFunction(this.model.get('results')[i])){ // This check is needed for IE8 for some reason
                if(this.cardsCache[this.model.get('results')[i]] != undefined && i >= this.maxResults){
                    card = this.cardsCache[this.model.get('results')[i]];
                    if(this.$(".globalSearchResultsMoreRows").hasClass("showing")){
                        card.$el.hide();
                        card.$el.slideDown();
                    }
                    else{
                        card.$el.slideUp();
                    }
                }
            }
        }
        $("#globalSearchInput").focus();
        this.parent.searchIndex = -1;
        this.parent.shift();
    },
    
    forceRoute: function(){
        if(typeof pageRouter != 'undefined'){
            pageRouter.navigate('/');
        }
    },
    
    events: {
        "click #showMoreResults": "toggleMoreResults",
        "click .card_link": "forceRoute"
    },
    
    renderResults: function(){
        this.$(".globalSearchResultsRows").empty();
        this.$(".showMore").hide();
        this.$(".globalSearchResultsMoreRows").empty();
        var html = '';
        var results = this.getResults();
        var extra = results.length - this.maxResults;
        for(i in results){
            var card = null;
            if(!_.isFunction(this.model.get('results')[i])){ // This check is needed for IE8 for some reason
                if(this.cardsCache[this.model.get('results')[i]] != undefined){
                    card = this.cardsCache[this.model.get('results')[i]];
                }
                else{
                    card = this.createCardView(this.createModel(this.model.get('results')[i]));
                    this.cardsCache[card.model.get('id')] = card;
                    card.render();
                    card.$el.hide();
                }
                if(i == this.model.get('selected')){
                    card.$el.find(".small_card").addClass('small_card_hover');
                }
                else{
                    card.$el.find(".small_card").removeClass('small_card_hover');
                }
                if(i < this.maxResults){
                    this.$el.find(".globalSearchResultsRows").append(card.$el);
                    card.$el.slideDown();
                }
                else{
                    this.$el.find(".globalSearchResultsMoreRows").append(card.$el);
                }
            }
        }
        this.$("#showMoreResults").attr('value', extra);
        if(extra > 0){
            if(this.$(".globalSearchResultsMoreRows").hasClass("showing")){
                this.$("#showMoreResults").text("Show fewer results");
            }
            else{
                this.$("#showMoreResults").text("Show " + this.$("#showMoreResults").attr('value') + " more results");
            }
            this.$(".showMore").show();
        }
        else {
            this.$(".showMore").hide();
        }
    }
});

PersonResultsView = ResultsView.extend({
    createCardView: function(model){
        return new SmallPersonCardView({model: model});
    },
    
    createModel: function(obj){
        return new Person({id: obj});
    },
    
    render: function(){
        this.$el.html(this.template({group: "People"}));
    }
});

ExpertResultsView = ResultsView.extend({
    maxResults: 3,

    createCardView: function(model){
        return new SmallPersonCardView({model: model});
    },
    
    createModel: function(obj){
        return new Person({id: obj});
    },
    
    render: function(){
        this.$el.html(this.template({group: "Experts"}));
    }
});

ProjectResultsView = ResultsView.extend({
    maxResults: 3,
    
    createCardView: function(model){
        return new SmallProjectCardView({model: model});
    },
    
    createModel: function(obj){
        return new Project({id: obj});
    },
    
    render: function(){
        this.$el.html(this.template({group: "Projects"}));
    }
});

ProductResultsView = ResultsView.extend({
    maxResults: 4,
    
    createCardView: function(model){
        return new SmallProductCardView({model: model});
    },
    
    createModel: function(obj){
        return new Product({id: obj});
    },
    
    render: function(){
        this.$el.html(this.template({group: productsTerm.pluralize()}));
    }
});

BibliographyResultsView = ResultsView.extend({
    maxResults: 3,
    
    createCardView: function(model){
        return new SmallBibliographyCardView({model: model});
    },
    
    createModel: function(obj){
        return new Bibliography({id: obj});
    },
    
    render: function(){
        this.$el.html(this.template({group: "Bibliographies"}));
    }
});

WikiResultsView = ResultsView.extend({
    maxResults: 3,

    createCardView: function(model){
        return new SmallWikiCardView({model: model});
    },
    
    createModel: function(obj){
        return new WikiPage({id: obj});
    },
    
    render: function(){
        this.$el.html(this.template({group: "Wiki Pages"}));
    }
});

PDFResultsView = ResultsView.extend({
    maxResults: 4,

    createCardView: function(model){
        return new SmallPDFCardView({model: model});
    },
    
    createModel: function(obj){
        return new PDF({id: obj});
    },
    
    render: function(){
        this.$el.html(this.template({group: "Reports"}));
    }
});
