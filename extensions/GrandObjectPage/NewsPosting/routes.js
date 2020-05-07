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
        "": "showNewsPostings",
        "new": "newNewsPosting",
        ":id": "showNewsPosting",
        ":id/edit": "editNewsPosting"
    }
});

// Initiate the router
var pageRouter = new PageRouter;

pageRouter.on('route:showNewsPostings', function (id) {
    // Get multiple NewsPostings
    var newses = new NewsPostings();
    this.closeCurrentView();
    this.currentView = new NewsPostingsView({el: $("#currentView"), model: newses});
});

pageRouter.on('route:newNewsPosting', function(){
    // Create New NewsPosting
    if(!me.isLoggedIn()){
        clearAllMessages();
        addError("You do not have permissions to view this page");
    }
    else{
        var news = new NewsPosting();
        this.closeCurrentView();
        this.currentView = new NewsPostingEditView({el: $("#currentView"), model: news});
    }
});

pageRouter.on('route:showNewsPosting', function (id) {
    // Get A single NewsPosting
    var news = new NewsPosting({'id': id});
    this.closeCurrentView();
    this.currentView = new NewsPostingView({el: $("#currentView"), model: news});
});

pageRouter.on('route:editNewsPosting', function (id) {
    // Get A single NewsPosting
    if(!me.isLoggedIn()){
        clearAllMessages();
        addError("You do not have permissions to view this page");
    }
    else{
        var news = new NewsPosting({'id': id});
        this.closeCurrentView();
        this.currentView = new NewsPostingEditView({el: $("#currentView"), model: news});
    }
});

// Start Backbone history a necessary step for bookmarkable URL's
Backbone.history.start();
