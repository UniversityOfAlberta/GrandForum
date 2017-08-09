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
        "": "showGrantAwards",
        "new": "newGrantAward",
        ":id": "showGrantAward",
        ":id/edit": "editGrantAward"
    }
});

// Initiate the router
var pageRouter = new PageRouter;

pageRouter.on('route:showGrantAwards', function(){
    // Show all Grant Awards (the set of grant awards will vary depending on the user)
    if(!me.isLoggedIn()){
        clearAllMessages();
        addError("You do not have permissions to view this page");
    }
    else{
        var grants = new GrantAwards();
        this.closeCurrentView();
        this.currentView = new GrantAwardsView({el: $("#currentView"), model: grants});
    }
});

pageRouter.on('route:newGrantAward', function () {
    // Get A single grant award
    if(!me.isLoggedIn()){
        clearAllMessages();
        addError("You do not have permissions to view this page");
    }
    else{
        var grant = new GrantAward({user_id: me.get('id')});
        this.closeCurrentView();
        this.currentView = new EditGrantAwardView({el: $("#currentView"), model: grant});
    }
});

pageRouter.on('route:showGrantAward', function (id) {
    // Get A single grant award
    var grant = new GrantAward({'id': id});
    this.closeCurrentView();
    this.currentView = new GrantAwardView({el: $("#currentView"), model: grant});
});

pageRouter.on('route:editGrantAward', function (id) {
    // Get A single grant award
    if(!me.isLoggedIn()){
        clearAllMessages();
        addError("You do not have permissions to view this page");
    }
    else{
        var grant = new GrantAward({'id': id});
        this.closeCurrentView();
        this.currentView = new EditGrantAwardView({el: $("#currentView"), model: grant});
    }
});

// Start Backbone history a necessary step for bookmarkable URL's
Backbone.history.start();
