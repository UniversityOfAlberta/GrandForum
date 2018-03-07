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
        "": "showCollaborations",
        "new": "newCollaboration",
        ":id": "showCollaboration",
        ":id/edit": "editCollaboration"
    }
    
});

// Initiate the router
var pageRouter = new PageRouter;

pageRouter.on('route:showCollaborations', function (id) {
    // Get A single Collaboration
    var collab = new Collaborations();
    this.closeCurrentView();
    this.currentView = new CollaborationsView({el: $("#currentView"), model: collab});
});

pageRouter.on('route:newCollaboration', function(){
    // Create New Collaboration
    if(!me.isLoggedIn()){
        clearAllMessages();
        addError("You do not have permissions to view this page");
    }
    else{
        var collab = new Collaboration();
        this.closeCurrentView();
        this.currentView = new CollaborationEditView({el: $("#currentView"), model: collab});
    }
});

pageRouter.on('route:showCollaboration', function (id) {
    // Get A single Collaboration
    var collab = new Collaboration({'id': id});
    this.closeCurrentView();
    this.currentView = new CollaborationView({el: $("#currentView"), model: collab});
});

pageRouter.on('route:editCollaboration', function (id) {
    // Get A single Collaboration
    if(!me.isLoggedIn()){
        clearAllMessages();
        addError("You do not have permissions to view this page");
    }
    else{
        var collab = new Collaboration({'id': id});
        this.closeCurrentView();
        this.currentView = new CollaborationEditView({el: $("#currentView"), model: collab});
    }
});

// Start Backbone history a necessary step for bookmarkable URL's
Backbone.history.start();
