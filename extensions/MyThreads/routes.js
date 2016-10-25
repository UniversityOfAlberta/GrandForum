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
        ":board_id": "viewBoard",
        ":board_id/:id": "viewThread",
        ":board_id/:id/edit": "editThread",
    }
});

// Initiate the router
var pageRouter = new PageRouter;

pageRouter.on('route:defaultRoute', function (actions) {
    main.set('title', 'Message Boards');
    this.closeCurrentView();
    var boards = new Boards();
    boards.fetch();
    this.currentView = new MyThreadsView({el: $("#currentView"), model: boards});
});

pageRouter.on('route:viewBoard', function(board_id) {
    this.closeCurrentView();
    var board = new Board({id: board_id});
    board.fetch();
    this.currentView = new BoardView({el: $("#currentView"), model: board});
});

pageRouter.on('route:viewThread', function (board_id, id) {
    this.closeCurrentView();
    var thread = new Thread({'id':id});
    this.currentView = new ThreadView({el: $("#currentView"), model: thread});
});

pageRouter.on('route:editThread', function (board_id, id) {
    this.closeCurrentView();
    var thread = new Thread({'id':id});
    this.currentView = new ThreadEditView({el: $("#currentView"), model: thread});
});


// Start Backbone history a necessary step for bookmarkable URL's
Backbone.history.start();
