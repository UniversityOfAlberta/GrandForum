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
        var contacts = new LIMSContactsPmm();
        this.closeCurrentView();
        this.currentView = new LIMSContactsTableViewPmm({el: $("#currentView"), model: contacts});
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
            var contact = new LIMSContactPmm();
            this.closeCurrentView();
            this.currentView = new LIMSContactEditViewPmm({el: $("#currentView"), model: contact});
            _.defer(this.currentView.render);
        }.bind(this));
    }
});

pageRouter.on('route:showLIMSContact', function(id){
    $.when(allPeopleXHR).done(function(){
        // Show a single LIMSContact
        var contact = new LIMSContactPmm({id: id});
        this.closeCurrentView();
        this.currentView = new LIMSContactViewPmm({el: $("#currentView"), model: contact});
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
            var contact = new LIMSContactPmm({id: id});
            this.closeCurrentView();
            this.currentView = new LIMSContactEditViewPmm({el: $("#currentView"), model: contact});
        }.bind(this));
    }
});

// Start Backbone history a necessary step for bookmarkable URL's
Backbone.history.start();
