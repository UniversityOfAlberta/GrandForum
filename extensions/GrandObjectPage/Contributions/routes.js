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
        "": "showContributions",
        "new": "newContribution",
        ":id": "showContribution",
        ":id/edit": "editContribution"
    }
});

// Initiate the router
var pageRouter = new PageRouter;

pageRouter.on('route:showContributions', function(){
    // Show all Grants (the set of grants will vary depending on the user)
    if(!me.isLoggedIn()){
        clearAllMessages();
        addError("You do not have permissions to view this page");
    }
    else{
        var contributions = new Contributions();
        this.closeCurrentView();
        this.currentView = new ContributionsView({el: $("#currentView"), model: contributions});
    }
});

pageRouter.on('route:showContribution', function (id) {
    // Get A single product
    var contribution = new Contribution({'id': id});
    this.closeCurrentView();
    this.currentView = new ContributionView({el: $("#currentView"), model: contribution});
});

pageRouter.on('route:editContribution', function (id) {
    // Get A single product
    var contribution = new Contribution({'id': id});
    this.closeCurrentView();
    this.currentView = new ContributionEditView({el: $("#currentView"), model: contribution});
});

pageRouter.on('route:newContribution', function () {
    // Get A single product
    var contribution = new Contribution();
    this.closeCurrentView();
    this.currentView = new ContributionEditView({el: $("#currentView"), model: contribution});
});

// Start Backbone history a necessary step for bookmarkable URL's
Backbone.history.start();
