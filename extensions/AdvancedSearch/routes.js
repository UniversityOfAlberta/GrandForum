PageRouter = Backbone.Router.extend({
 
    currentView: null,

    initialize: function(){
        //this.bind('all', function(event){
            //$("#currentView").html("<div id='currentViewSpinner'></div>");
            //spin = spinner("currentViewSpinner", 40, 75, 12, 10, '#888');
            //this.currentView = new SearchView({el: $("#currentView")/*, model: product*/});
        //});
        this.currentView = new SearchView({el: $("#currentView"), page_num: 0});
    },
    
    closeCurrentView: function(){
        if(this.currentView != null){
            clearAllMessages();
            this.currentView.unbind();
            this.currentView.remove();
            $("div#backbone_main").append("<div id='currentView' />");
        }
    },

    routes: {
        "page/:page_num": "getPage",
        "*actions": "defaultRoute"
    }
});

// Initiate the router
var pageRouter = new PageRouter;

pageRouter.on('route:getPage', function (page_num) {
    if(this.currentView.options.page_num != page_num){
        this.currentView.options.page_num = page_num;
        this.currentView.doSearch();
    }
    //this.currentView = new SearchView({el: $("#currentView"), current_page: page_num});
});

pageRouter.on('route:defaultRoute', function (actions) {
    this.currentView.options.page_num = 1;
    // /this.currentView = new SearchView({el: $("#currentView"), page_num: 0});
});


// Start Backbone history a necessary step for bookmarkable URL's
Backbone.history.start();