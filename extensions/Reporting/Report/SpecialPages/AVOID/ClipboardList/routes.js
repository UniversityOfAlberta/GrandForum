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
    }
});

// Initiate the router
var pageRouter = new PageRouter;

pageRouter.on('route:defaultRoute', function (actions) {
    main.set('title', 'Clipboard');
    if(wgLang == 'fr'){
        main.set('title', 'Localiser une Pharmacie');
    }
    this.closeCurrentView();
    //var pharms = new Universities();
    var clipboard = new PersonClipboard();
    clipboard.fetch();
    console.log(clipboard);
    this.currentView = new ClipboardListView({el: $("#currentView"), model: clipboard});
});

// Start Backbone history a necessary step for bookmarkable URL's
Backbone.history.start();
