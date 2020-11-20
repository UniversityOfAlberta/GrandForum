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
        "": "showCRM",
    }
});

// Initiate the router
var pageRouter = new PageRouter;

pageRouter.on('route:showCRM', function(){
    // Get All Products
    var contacts = new CRMContacts();
    
    main.set('title', "Contacts");
    this.closeCurrentView();
    this.currentView = new CRMContactsTable({el: $("#currentView"), model: contacts});
});

// Start Backbone history a necessary step for bookmarkable URL's
Backbone.history.start();
