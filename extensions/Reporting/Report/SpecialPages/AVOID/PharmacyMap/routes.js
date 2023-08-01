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
        "mylocation": "mylocation", 
        ":category_code": "viewCategory"
    }
});

// Initiate the router
var pageRouter = new PageRouter;

pageRouter.on('route:defaultRoute', function (actions) {
    main.set('title', 'Locate a Community Resource');
    if(wgLang == 'fr'){
        main.set('title', 'Localiser une Pharmacie');
    }
    this.closeCurrentView();
    var pharms = new AvoidResources();
    pharms.fetch();
    this.currentView = new PharmacyMapView({el: $("#currentView"), model: pharms});
});

pageRouter.on('route:mylocation', function (actions) {
    main.set('title', 'Locate a Community Resource');
    if(wgLang == 'fr'){
        main.set('title', 'Localiser une Pharmacie');
    }
    this.closeCurrentView();
    var pharms = new AvoidResources();
    navigator.geolocation.getCurrentPosition(function (position) {
        pharms.lat = position.coords.latitude;
        pharms.long = position.coords.longitude;
        pharms.fetch();
        this.currentView = new PharmacyMapView({el: $("#currentView"), model: pharms});
    }.bind(this), function () {
        pharms.fetch();
        this.currentView = new PharmacyMapView({el: $("#currentView"), model: pharms});
    }.bind(this));
});


pageRouter.on('route:viewCategory', function(category_code) {
    main.set('title', 'Locate a Community Resource');
    if(wgLang == 'fr'){
        main.set('title', 'Localiser une Pharmacie');
    }
    this.closeCurrentView();
    var pharms = new AvoidResources();
    pharms.cat = category_code;
    this.currentView = new PharmacyMapView({el: $("#currentView"), model: pharms});
    this.currentView.addCategoryButtons();
});


// Start Backbone history a necessary step for bookmarkable URL's
Backbone.history.start();
