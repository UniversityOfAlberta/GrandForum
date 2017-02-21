PageRouter = Backbone.Router.extend({
    
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
        "new": "newBibliography",
        ":id": "showBibliography",
        ":id/edit": "editBibliography"
    }
});

// Initiate the router
var pageRouter = new PageRouter;

pageRouter.on('route:newBibliography', function(){
    // Create New Bibliography
    if(!me.isLoggedIn()){
        clearAllMessages();
        addError("You do not have permissions to view this page");
    }
    else{
        var bib = new Bibliography();
        this.closeCurrentView();
        this.currentView = new BibliographyEditView({el: $("#currentView"), model: bib});
    }
});

pageRouter.on('route:showBibliography', function (id) {
    // Get A single Bibliography
    var bib = new Bibliography({'id': id});
    this.closeCurrentView();
    this.currentView = new BibliographyView({el: $("#currentView"), model: bib});
});

pageRouter.on('route:editBibliography', function (id) {
    // Get A single Bibliography
    if(!me.isLoggedIn()){
        clearAllMessages();
        addError("You do not have permissions to view this page");
    }
    else{
        var bib = new Bibliography({'id': id});
        this.closeCurrentView();
        this.currentView = new BibliographyEditView({el: $("#currentView"), model: bib});
    }
});

// Start Backbone history a necessary step for bookmarkable URL's
Backbone.history.start();
