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
	":id":"viewStory",
	":id/edit":"editStory",
    }
});

// Initiate the router
var pageRouter = new PageRouter;

pageRouter.on('route:defaultRoute', function (actions) {
    main.set('title', 'Discussion Room');
   if(wgLang == "fr"){
	main.set('title', "Salle de discussion");
   }
    this.closeCurrentView();
    var stories = new Stories();
    stories.fetch();
    this.currentView = new ManageStoriesView({el: $("#currentView"), model: stories});
});

pageRouter.on('route:viewStory', function (id) {
    main.set('title', 'Case or Experience');
    this.closeCurrentView();
    var story = new Story({'id':id});
    this.closeCurrentView();
    this.currentView = new StoryView({el: $("#currentView"), model: story});
});

pageRouter.on('route:editStory', function (id) {
    if(!me.isLoggedIn()){
        clearAllMessages();
        addError("You do not have permissions to view this page");
    }
    else{
        var story = new Story({'id': id});
        this.closeCurrentView();
        this.currentView = new StoryEditView({el: $("#currentView"), model: story});
    }
});

// Start Backbone history a necessary step for bookmarkable URL's
Backbone.history.start();
