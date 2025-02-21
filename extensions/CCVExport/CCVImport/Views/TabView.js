TabView = Backbone.View.extend({

    template: _.template($("#tab_template").html()),
    currentRoles: null,

    initialize: function(options){
	var self = this;
	me.getRoles();
	me.roles.ready().then(function(){
	    this.currentRoles = me.roles.getCurrent();
	    me.roles.ready().then(function(){
	        Backbone.Subviews.add(this);
                var intervalId = setInterval(function(){
                    if($('#ccvtab').is(':visible')){
                        self.subviews.ccvImport.render();
                        clearInterval(intervalId);
                        intervalId = null;
                    }
                 }, 100);
                var intervalId1 = setInterval(function(){
                    if($('#csvtab').is(':visible')){
                        self.subviews.csvImport.render();
                        clearInterval(intervalId1);
                        intervalId1 = null;
                    }
                 }, 100);
                 var intervalId2 = setInterval(function(){
                    if($('#evalstab').is(':visible')){
                        self.subviews.courseEvalImport.render();
                        clearInterval(intervalId2);
                        intervalId2 = null;
                    }
                 }, 100);
                var intervalId3 = setInterval(function(){
                    if($('#grantstab').is(':visible')){
                        self.subviews.grantImport.render();
                        clearInterval(intervalId3);
                        intervalId3 = null;
                    }
                 }, 100);
		        var intervalId4 = setInterval(function(){
                    if($('#coursetab').is(':visible')){
                        self.subviews.courseFileImport.render();
                        clearInterval(intervalId4);
                        intervalId4 = null;
                    }
                 }, 100);
	         this.render();
	    }.bind(this)) 
	}.bind(this));
    },

    subviewCreators: {
	    "ccvImport" : function(){
	     return new CCVImportView({parent: this, model: new CCVImportModel()});
	    },
        "csvImport" : function(){
             return new CSVImportView({parent: this, model: new CCVImportModel()});
        },
        "grantImport" : function(){
             return new GrantImportView({parent: this, model: new CCVImportModel()});
        },
        "courseEvalImport" : function(){
             return new EvalImportView({parent: this, model: new CCVImportModel()});
        },
        "courseFileImport" : function(){
             return new CourseFileImportView({parent: this, model: new CCVImportModel()});
        }
    },   

 
    events: {
    },
    
    render: function(){
	    this.$el.html(this.template(this.model.toJSON()));
        $( "#tabs" ).tabs();
	    return this.$el;
    }

});
