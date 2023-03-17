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

allPeople = new People();
allPeople.simple = true;
allPeople.roles = [STAFF,MANAGER,ADMIN];
allPeopleXHR = allPeople.fetch();

pageRouter.on('route:showCRMContactsTable', function(){
    main.set('title', "Manage CRM");
    $.when(allPeopleXHR).done(function(){
        // Get All CRMContacts
        var contacts = new CRMContacts();
        this.closeCurrentView();
        this.currentView = new CRMContactsTableView({el: $("#currentView"), model: contacts});
    }.bind(this));
});

pageRouter.on('route:newCRMContact', function(){
    // Create New CRMContact
    if(!me.isLoggedIn()){
        clearAllMessages();
        addError("You do not have permissions to view this page");
    }
    else{
        $.when(allPeopleXHR).done(function(){
            var contact = new CRMContact();
            this.closeCurrentView();
            this.currentView = new CRMContactEditView({el: $("#currentView"), model: contact});
            _.defer(this.currentView.render);
        }.bind(this));
    }
});

pageRouter.on('route:showCRMContact', function(id){
    $.when(allPeopleXHR).done(function(){
        // Show a single CRMContact
        var contact = new CRMContact({id: id});
        this.closeCurrentView();
        this.currentView = new CRMContactView({el: $("#currentView"), model: contact});
    }.bind(this));
});

pageRouter.on('route:editCRMContact', function (id) {
    // Edit a single CRMContact
    if(!me.isLoggedIn()){
        clearAllMessages();
        addError("You do not have permissions to view this page");
    }
    else{
        $.when(allPeopleXHR).done(function(){
            var contact = new CRMContact({id: id});
            this.closeCurrentView();
            this.currentView = new CRMContactEditView({el: $("#currentView"), model: contact});
        }.bind(this));
    }
});

// Start Backbone history a necessary step for bookmarkable URL's
Backbone.history.start();
