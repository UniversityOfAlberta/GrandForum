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
        "*actions": "defaultRoute"
    }
});

// Initiate the router
var pageRouter = new PageRouter;

pageRouter.on('route:defaultRoute', function (actions) {
    if(_.where(me.get('roles'), {role: HQP}).length > 0){
        main.set('title', 'Supervisor Management');
        this.closeCurrentView();
        $("#currentView").css("border", "1px solid #AAAAAA");
        this.currentView = new ManagePeopleEditUniversitiesView({el: $("#currentView"), model: me.universities, 
                                                                                        person: me,
                                                                                        hqpView: true});
        $("#currentView").after("<input type='button' id='addUniversity' value='Add Institution' /><br /><input type='button' id='save' value='Save' />");
        $('#addUniversity').click(function(){
            this.currentView.addUniversity();
        }.bind(this));
        $('#save').click(function(){
            this.currentView.saveAll();
        }.bind(this));
    }
    else{
        main.set('title', 'HQP Management');
        this.closeCurrentView();
        var people = new People();
        people.roles = ['managed'];
        people.fetch({reset: true});
        this.currentView = new ManagePeopleView({el: $("#currentView"), model: people});
    }
});

// Start Backbone history a necessary step for bookmarkable URL's
Backbone.history.start();
