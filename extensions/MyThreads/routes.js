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
        ":id": "viewThread",
        ":id/edit": "editThread",
    }
});

// Initiate the router
var pageRouter = new PageRouter;

pageRouter.on('route:defaultRoute', function (actions) {
    main.set('title', 'Message Boards');
    this.closeCurrentView();
    var threads = new Threads();
    threads.fetch();
    this.currentView = new MyThreadsView({el: $("#currentView"), model: threads});
});

pageRouter.on('route:viewThread', function (id) {
    this.closeCurrentView();
    var thread = new Thread({'id':id});
    this.currentView = new ThreadView({el: $("#currentView"), model: thread});
});

pageRouter.on('route:editThread', function (id) {
    this.closeCurrentView();
    var thread = new Thread({'id':id});
    this.currentView = new ThreadEditView({el: $("#currentView"), model: thread});
});


// Start Backbone history a necessary step for bookmarkable URL's
Backbone.history.start();
