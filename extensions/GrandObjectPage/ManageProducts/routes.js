PageRouter = Backbone.Router.extend({

    currentView: null,
    
    initialize: function(){
        this.bind('all', function(event){
            $("#currentView").html("<div id='currentViewSpinner'></div>");
            spin = spinner("currentViewSpinner", 40, 75, 12, 10, '#888');
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
        "all": "showAll",
        "*actions": "defaultRoute"
    }
});

// Initiate the router
var pageRouter = new PageRouter;

pageRouter.on('route:defaultRoute', function (actions) {
    main.set('title', productsTerm + ' Management');
    this.closeCurrentView();
    var products = me.getManagedProducts();
    products.all = false;
    this.currentView = new ManageProductsView({el: $("#currentView"), model: products});
});

pageRouter.on('route:showAll', function (actions) {
    main.set('title', productsTerm + ' Management');
    this.closeCurrentView();
    var products = me.getAdminProducts();
    products.all = true;
    this.currentView = new ManageProductsView({el: $("#currentView"), model: products});
});

// Start Backbone history a necessary step for bookmarkable URL's
Backbone.history.start();
