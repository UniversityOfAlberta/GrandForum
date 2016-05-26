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
	"add": "addPharm",
    }
});

// Initiate the router
var pageRouter = new PageRouter;

pageRouter.on('route:defaultRoute', function (actions) {
    main.set('title', 'Locate a Pharmacy');
    if(wgLang == 'fr'){
        main.set('title', 'Localiser une Pharmacie');
    }
    this.closeCurrentView();
    var pharms = new Universities();
    pharms.fetch();
    this.currentView = new PharmacyMapView({el: $("#currentView"), model: pharms});
});

pageRouter.on('route:addPharm', function (actions) {
    main.set('title', 'Add a Pharmacy');
    if(wgLang == 'fr'){
        main.set('title', 'Ajouter une Pharmacie');
    }
    this.closeCurrentView();
    var pharms = new University();
    pharms.fetch();
    this.currentView = new PharmacyAddView({el: $("#currentView"), model: pharms});
});



// Start Backbone history a necessary step for bookmarkable URL's
Backbone.history.start();
