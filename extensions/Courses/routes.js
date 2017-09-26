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
        ":id": "viewCourse",
	":id/edit": "editCourse",
    }
});

// Initiate the router
var pageRouter = new PageRouter;

pageRouter.on('route:defaultRoute', function (actions) {
    main.set('title', 'Courses');
    this.closeCurrentView();
    var courses = new Courses();
    courses.fetch();
    this.currentView = new CoursesView({el: $("#currentView"), model: courses});
});

pageRouter.on('route:viewCourse', function (id) {
    this.closeCurrentView();
    var course = new Course({'id':id});
    course.fetch();
    this.currentView = new CourseView({el: $("#currentView"), model: course});
});

pageRouter.on('route:editCourse', function (id) {
    this.closeCurrentView();
    var course = new Course({'id':id});
    course.fetch();
    this.currentView = new CoursesEditView({el: $("#currentView"), model: course});
});




// Start Backbone history a necessary step for bookmarkable URL's
Backbone.history.start();

