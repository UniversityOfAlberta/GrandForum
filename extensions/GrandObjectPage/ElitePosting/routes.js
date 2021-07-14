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
        "": "showElitePostings",
        "new": "newElitePosting",
        "admin": "showElitePostingsAdmin",
        ":id": "showElitePosting",
        ":id/edit": "editElitePosting"
    }
});

// Initiate the router
var pageRouter = new PageRouter;

pageRouter.on('route:showElitePostings', function (id) {
    // Get multiple ElitePostings
    var postings = new ElitePostings();
    this.closeCurrentView();
    this.currentView = new ElitePostingsView({el: $("#currentView"), model: postings});
});

pageRouter.on('route:showElitePostingsAdmin', function (id) {
    // Get multiple ElitePostings
    var postings = new ElitePostings();
    this.closeCurrentView();
    this.currentView = new ElitePostingsAdminView({el: $("#currentView"), model: postings});
});

pageRouter.on('route:newElitePosting', function(){
    // Create New ElitePosting
    if(!me.isLoggedIn()){
        clearAllMessages();
        addError("You do not have permissions to view this page");
    }
    else{
        var posting = new ElitePosting();
        this.closeCurrentView();
        this.currentView = new ElitePostingEditView({el: $("#currentView"), model: posting});
    }
});

pageRouter.on('route:showElitePosting', function (id) {
    // Get A single ElitePosting
    var posting = new ElitePosting({'id': id});
    this.closeCurrentView();
    this.currentView = new ElitePostingView({el: $("#currentView"), model: posting});
});

pageRouter.on('route:editElitePosting', function (id) {
    // Get A single ElitePosting
    if(!me.isLoggedIn()){
        clearAllMessages();
        addError("You do not have permissions to view this page");
    }
    else{
        var posting = new ElitePosting({'id': id});
        this.closeCurrentView();
        this.currentView = new ElitePostingEditView({el: $("#currentView"), model: posting});
    }
});

// Start Backbone history a necessary step for bookmarkable URL's
Backbone.history.start();
