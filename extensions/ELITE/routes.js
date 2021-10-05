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
        "intern": "showHostElitePostings",
        "intern/new": "newHostElitePosting",
        "phd": "showPhDElitePostings",
        "phd/new": "newPhDElitePosting",
        "admin": "showEliteAdmin",
        ":id": "showElitePosting",
        ":id/edit": "editElitePosting"
    }
});

// Initiate the router
var pageRouter = new PageRouter;

pageRouter.on('route:showHostElitePostings', function (id) {
    // Get multiple ElitePostings
    this.closeCurrentView();
    this.currentView = new EliteHostView({type: "Intern", el: $("#currentView")});
    _.defer(this.currentView.render.bind(this.currentView));
});

pageRouter.on('route:showPhDElitePostings', function (id) {
    // Get multiple ElitePostings
    this.closeCurrentView();
    this.currentView = new EliteHostView({type: "PhD", el: $("#currentView")});
    _.defer(this.currentView.render.bind(this.currentView));
});

pageRouter.on('route:newHostElitePosting', function(){
    // Create New ElitePosting
    if(!me.isLoggedIn()){
        clearAllMessages();
        addError("You do not have permissions to view this page");
    }
    else{
        var posting = new ElitePosting({type: "Intern"});
        posting.type = "Intern";
        this.closeCurrentView();
        this.currentView = new ElitePostingEditView({el: $("#currentView"), model: posting});
    }
});

pageRouter.on('route:newPhDElitePosting', function(){
    // Create New ElitePosting
    if(!me.isLoggedIn()){
        clearAllMessages();
        addError("You do not have permissions to view this page");
    }
    else{
        var posting = new ElitePosting({type: "PhD"});
        this.closeCurrentView();
        this.currentView = new ElitePostingEditView({el: $("#currentView"), model: posting});
    }
});

pageRouter.on('route:showEliteAdmin', function (id) {
    // Get multiple ElitePostings
    this.closeCurrentView();
    this.currentView = new EliteAdminView({el: $("#currentView")});
    _.defer(this.currentView.render.bind(this.currentView));
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
