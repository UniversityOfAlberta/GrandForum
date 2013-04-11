PageRouter = Backbone.Router.extend({
    
    initialize: function(){
        this.bind('all', function(event){
           // $("#currentView").html("<div id='currentViewSpinner'></div>");
           // spin = spinner("currentViewSpinner", 40, 75, 12, 10, '#888');
        this.currentView = new SearchView({el: $("#currentView")/*, model: product*/});
        });

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
        "*actions": "defaultRoute"
    }
});

// Initiate the router
var pageRouter = new PageRouter;

pageRouter.on('route:defaultRoute', function (actions) {
    // Get A single product
    //product = new Product({'id': id});
    
    //this.closeCurrentView();
    //this.currentView = new SearchView({el: $("#currentView")/*, model: product*/});
});

// Start Backbone history a necessary step for bookmarkable URL's
Backbone.history.start();