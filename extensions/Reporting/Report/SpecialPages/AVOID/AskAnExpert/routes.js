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
        "": "defaultRoute", 
	":id/edit":"editRoute",
	":id/register":"registerRoute",
    }
});

// Initiate the router
var pageRouter = new PageRouter;

pageRouter.on('route:defaultRoute', function (actions) {
    main.set('title', 'Ask An Expert');
    this.closeCurrentView();
    var pharms = new AskAnExpertEvents();
    pharms.fetch();
    //change later to accomodate for more than 1 event if needed
    this.currentView = new ExpertDashboardView({el: $("#currentView"), model: pharms});
});

pageRouter.on('route:editRoute', function (id) {
    main.set('title', 'Ask An Expert Edit');
    this.closeCurrentView();
    var pharms = new AskAnExpertEvent();
    //change this later
    pharms.fetch();
    this.currentView = new ExpertEditView({el: $("#currentView"), model: pharms});
});

pageRouter.on('route:registerRoute', function (id) {
    main.set('title', 'Ask An Expert Registration');
    this.closeCurrentView();
    var pharms = new AskAnExpertEvent();
    pharms.id = id;
    //change this later
    pharms.fetch();
    this.currentView = new EventRegisterView({el: $("#currentView"), model: pharms});
});


// Start Backbone history a necessary step for bookmarkable URL's
Backbone.history.start();
