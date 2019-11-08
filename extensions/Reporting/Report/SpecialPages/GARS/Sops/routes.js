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
        "(:year)": "defaultRoute",
        ":id/edit": "editSop",
    }
});

// Initiate the router
var pageRouter = new PageRouter;

pageRouter.on('route:defaultRoute', function (year) {
    main.set('title', '');
    this.closeCurrentView();
    var gsms = new GsmsDataAll();
    gsms.folder = 'all';
    gsms.year = (year != null) ? year : "";
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

