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
        "": "showCRMContactsTable",
        "new": "newCRMContact",
        ":id": "showCRMContact",
        ":id/edit": "editCRMContact"
    }
});

// Initiate the router
var pageRouter = new PageRouter;

pageRouter.on('route:showCRMContactsTable', function(){
    // Get All CRMContacts
    var contacts = new CRMContacts();
    
    main.set('title', "Contacts");
    this.closeCurrentView();
    this.currentView = new CRMContactsTableView({el: $("#currentView"), model: contacts});
});

pageRouter.on('route:newCRMContact', function(){
    // Create New CRMContact
    if(!me.isLoggedIn()){
        clearAllMessages();
        addError("You do not have permissions to view this page");
    }
    else{
        var contact = new CRMContact();
        this.closeCurrentView();
        this.currentView = new CRMContactEditView({el: $("#currentView"), model: contact});
    }
});

pageRouter.on('route:showCRMContact', function(id){
    // Show a single CRMContact
    var contact = new CRMContact({id: id});

    this.closeCurrentView();
    this.currentView = new CRMContactView({el: $("#currentView"), model: contact});
});

pageRouter.on('route:editCRMContact', function (id) {
    // Edit a single CRMContact
    if(!me.isLoggedIn()){
        clearAllMessages();
        addError("You do not have permissions to view this page");
    }
    else{
        var contact = new CRMContact({id: id});
        this.closeCurrentView();
        this.currentView = new CRMContactEditView({el: $("#currentView"), model: contact});
    }
});

// Start Backbone history a necessary step for bookmarkable URL's
Backbone.history.start();
