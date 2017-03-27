PageRouter = Backbone.Router.extend({
    
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
        ":id": "showGrant",
        ":id/edit": "editGrant"
    }
});

// Initiate the router
var pageRouter = new PageRouter;

pageRouter.on('route:showGrant', function (id) {
    // Get A single grant
    var grant = new Grant({'id': id});
    this.closeCurrentView();
    this.currentView = new GrantView({el: $("#currentView"), model: grant});
});

pageRouter.on('route:editGrant', function (id) {
    // Get A single grant
    if(!me.isLoggedIn()){
        clearAllMessages();
        addError("You do not have permissions to view this page");
    }
    else{
        var grant = new Grant({'id': id});
        this.closeCurrentView();
        this.currentView = new EditGrantView({el: $("#currentView"), model: grant});
    }
});

// Start Backbone history a necessary step for bookmarkable URL's
Backbone.history.start();
