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
        "": "showEventPostings",
        "new": "newEventPosting",
        ":id": "showEventPosting",
        ":id/edit": "editEventPosting"
    }
});

// Initiate the router
var pageRouter = new PageRouter;

pageRouter.on('route:showEventPostings', function (id) {
    // Get multiple EventPostings
    var events = new EventPostings();
    this.closeCurrentView();
    this.currentView = new EventPostingsView({el: $("#currentView"), model: events});
});

pageRouter.on('route:newEventPosting', function(){
    // Create New EventPosting
    if(!me.isLoggedIn()){
        clearAllMessages();
        addError("You do not have permissions to view this page");
    }
    else{
        var event = new EventPosting();
        this.closeCurrentView();
        this.currentView = new EventPostingEditView({el: $("#currentView"), model: event});
    }
});

pageRouter.on('route:showEventPosting', function (id) {
    // Get A single EventPosting
    var event = new EventPosting({'id': id});
    this.closeCurrentView();
    this.currentView = new EventPostingView({el: $("#currentView"), model: event});
});

pageRouter.on('route:editEventPosting', function (id) {
    // Get A single EventPosting
    if(!me.isLoggedIn()){
        clearAllMessages();
        addError("You do not have permissions to view this page");
    }
    else{
        var event = new EventPosting({'id': id});
        this.closeCurrentView();
        this.currentView = new EventPostingEditView({el: $("#currentView"), model: event});
    }
});

// Start Backbone history a necessary step for bookmarkable URL's
Backbone.history.start();
