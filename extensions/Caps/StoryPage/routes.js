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
        ":id": "defaultRoute",
        ":idi/edit": "editStory"

    }
});

// Initiate the router
var pageRouter = new PageRouter;

pageRouter.on('route:defaultRoute', function (id) {
    main.set('title', 'Story');
    this.closeCurrentView();
    var story = new Story({'id':id});
    this.closeCurrentView();
    story.fetch();
    this.currentView = new StoryView({el: $("#currentView"), model: story});
});

pageRouter.on('route:editStory', function (category, id) {
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
