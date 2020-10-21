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
        "": "showBSIPostings",
        "new": "newBSIPosting",
        ":id": "showBSIPosting",
        ":id/edit": "editBSIPosting"
    }
});

// Initiate the router
var pageRouter = new PageRouter;

pageRouter.on('route:showBSIPostings', function (id) {
    // Get multiple BSIPostings
    var postings = new BSIPostings();
    this.closeCurrentView();
    this.currentView = new BSIPostingsView({el: $("#currentView"), model: postings});
});

pageRouter.on('route:newBSIPosting', function(){
    // Create New BSIPosting
    if(!me.isLoggedIn()){
        clearAllMessages();
        addError("You do not have permissions to view this page");
    }
    else{
        var posting = new BSIPosting();
        this.closeCurrentView();
        this.currentView = new BSIPostingEditView({el: $("#currentView"), model: posting});
    }
});

pageRouter.on('route:showBSIPosting', function (id) {
    // Get A single BSIPosting
    var posting = new BSIPosting({'id': id});
    this.closeCurrentView();
    this.currentView = new BSIPostingView({el: $("#currentView"), model: posting});
});

pageRouter.on('route:editBSIPosting', function (id) {
    // Get A single BSIPosting
    if(!me.isLoggedIn()){
        clearAllMessages();
        addError("You do not have permissions to view this page");
    }
    else{
        var posting = new BSIPosting({'id': id});
        this.closeCurrentView();
        this.currentView = new BSIPostingEditView({el: $("#currentView"), model: posting});
    }
});

// Start Backbone history a necessary step for bookmarkable URL's
Backbone.history.start();
