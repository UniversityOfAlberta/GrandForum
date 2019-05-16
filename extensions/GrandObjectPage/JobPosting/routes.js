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
        "": "showJobPostings",
        "new": "newJobPosting",
        ":id": "showJobPosting",
        ":id/edit": "editJobPosting"
    }
});

// Initiate the router
var pageRouter = new PageRouter;

pageRouter.on('route:showJobPostings', function (id) {
    // Get multiple JobPostings
    var jobs = new JobPostings();
    this.closeCurrentView();
    this.currentView = new JobPostingsView({el: $("#currentView"), model: jobs});
});

pageRouter.on('route:newJobPosting', function(){
    // Create New JobPosting
    if(!me.isLoggedIn()){
        clearAllMessages();
        addError("You do not have permissions to view this page");
    }
    else{
        var job = new JobPosting();
        this.closeCurrentView();
        this.currentView = new JobPostingEditView({el: $("#currentView"), model: job});
    }
});

pageRouter.on('route:showJobPosting', function (id) {
    // Get A single JobPosting
    var job = new JobPosting({'id': id});
    this.closeCurrentView();
    this.currentView = new JobPostingView({el: $("#currentView"), model: job});
});

pageRouter.on('route:editJobPosting', function (id) {
    // Get A single JobPosting
    if(!me.isLoggedIn()){
        clearAllMessages();
        addError("You do not have permissions to view this page");
    }
    else{
        var job = new JobPosting({'id': id});
        this.closeCurrentView();
        this.currentView = new JobPostingEditView({el: $("#currentView"), model: job});
    }
});

// Start Backbone history a necessary step for bookmarkable URL's
Backbone.history.start();
