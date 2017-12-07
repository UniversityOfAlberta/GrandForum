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
        ":id": "showContribution"
    }
});

// Initiate the router
var pageRouter = new PageRouter;

pageRouter.on('route:showContribution', function (id) {
    // Get A single product
    var contribution = new Contribution({'id': id});
    this.closeCurrentView();
    this.currentView = new ContributionView({el: $("#currentView"), model: contribution});
});

// Start Backbone history a necessary step for bookmarkable URL's
Backbone.history.start();
