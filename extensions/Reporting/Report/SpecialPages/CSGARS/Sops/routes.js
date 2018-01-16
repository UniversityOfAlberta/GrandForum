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
        "reviewInProgress": "defaultRoute",
        "inProgress": "inProgress",
        "newApplications": "newApplications",
        "other": "other",
        ":id/edit": "editSop",
    }
});

// Initiate the router
var pageRouter = new PageRouter;

pageRouter.on('route:defaultRoute', function (actions) {
    main.set('title', '');
    this.closeCurrentView();
    var gsms = new GsmsDataAll();
    gsms.folder = "Review in Progress";
    gsms.fetch();
    this.currentView = new SopsView({el: $("#currentView"), model: gsms});
});

pageRouter.on('route:inProgress', function (actions) {
    main.set('title', '');
    this.closeCurrentView();
    var gsms = new GsmsDataAll();
    gsms.folder = "In Progress";
    gsms.fetch();
    this.currentView = new SopsView({el: $("#currentView"), model: gsms});
});

pageRouter.on('route:newApplications', function (actions) {
    main.set('title', '');
    this.closeCurrentView();
    var gsms = new GsmsDataAll();
    gsms.folder = "New Applications";
    gsms.fetch();
    this.currentView = new SopsView({el: $("#currentView"), model: gsms});
});

pageRouter.on('route:other', function (actions) {
    main.set('title', '');
    this.closeCurrentView();
    var gsms = new GsmsDataAll();
    gsms.folder = "";
    gsms.fetch();
    this.currentView = new SopsView({el: $("#currentView"), model: gsms});
});

pageRouter.on('route:editSop', function (id) {
    main.set('title', '');
    this.closeCurrentView();
    var sop = new Sop({'id':id});
    sop.fetch();
    this.currentView = new SopsEditView({el: $("#currentView"), model: sop});
});


// Start Backbone history a necessary step for bookmarkable URL's
Backbone.history.start();

