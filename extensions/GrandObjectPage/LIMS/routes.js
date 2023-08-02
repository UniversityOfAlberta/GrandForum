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
        "": "showLIMSContactsTable",
        "new": "newLIMSContact",
        ":id": "showLIMSContact",
        ":id/edit": "editLIMSContact"
    }
});

// Initiate the router
var pageRouter = new PageRouter;

allPeople = new People();
allPeople.simple = true;
allPeople.roles = [STAFF,MANAGER,ADMIN];
allPeopleXHR = allPeople.fetch();

pageRouter.on('route:showLIMSContactsTable', function(){
    main.set('title', "Manage LIMS");
    $.when(allPeopleXHR).done(function(){
        // Get All LIMSContacts
        var contacts = new LIMSContacts();
        this.closeCurrentView();
        this.currentView = new LIMSContactsTableView({el: $("#currentView"), model: contacts});
    }.bind(this));
});

pageRouter.on('route:newLIMSContact', function(){
    // Create New LIMSContact
    if(!me.isLoggedIn()){
        clearAllMessages();
        addError("You do not have permissions to view this page");
    }
    else{
        $.when(allPeopleXHR).done(function(){
            var contact = new LIMSContact();
            this.closeCurrentView();
            this.currentView = new LIMSContactEditView({el: $("#currentView"), model: contact});
            _.defer(this.currentView.render);
        }.bind(this));
    }
});

pageRouter.on('route:showLIMSContact', function(id){
    $.when(allPeopleXHR).done(function(){
        // Show a single LIMSContact
        var contact = new LIMSContact({id: id});
        this.closeCurrentView();
        this.currentView = new LIMSContactView({el: $("#currentView"), model: contact});
    }.bind(this));
});

pageRouter.on('route:editLIMSContact', function (id) {
    // Edit a single LIMSContact
    if(!me.isLoggedIn()){
        clearAllMessages();
        addError("You do not have permissions to view this page");
    }
    else{
        $.when(allPeopleXHR).done(function(){
            var contact = new LIMSContact({id: id});
            this.closeCurrentView();
            this.currentView = new LIMSContactEditView({el: $("#currentView"), model: contact});
        }.bind(this));
    }
});

// Start Backbone history a necessary step for bookmarkable URL's
Backbone.history.start();
