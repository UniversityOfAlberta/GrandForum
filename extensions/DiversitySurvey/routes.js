PageRouter = Backbone.Router.extend({
    
    currentView: null,
        
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
        "faq": "faq",
        "*actions": "defaultRoute"
    }
});

// Initiate the router
var pageRouter = new PageRouter;

pageRouter.on('route:defaultRoute', function (actions) {
    var diversity = new Diversity();

    this.closeCurrentView();
    this.currentView = new DiversitySurveyView({el: $("#currentView"), model: diversity});
});

pageRouter.on('route:faq', function (actions) {
    var diversity = new Diversity();

    this.closeCurrentView();
    this.currentView = new DiversityFaqView({el: $("#currentView"), model: diversity});
});

// Start Backbone history a necessary step for bookmarkable URL's
Backbone.history.start();
