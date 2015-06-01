ManagePeopleEditProjectsView = Backbone.View.extend({

    projects: null,
    person: null,

    initialize: function(options){
        this.person = options.person;
        this.model.fetch();
        //this.listenTo(this.model, "change", this.render);
        this.template = _.template($('#edit_projects_template').html());
        this.model.ready().then($.proxy(function(){
            this.projects = this.model;
            this.listenTo(this.projects, "add", this.addRows);
            this.model.ready().then($.proxy(function(){
                this.render();
            }, this));
        }, this));
        
        var dims = {w:0, h:0};
        // Reposition the dialog when the window is resized or the dialog is resized
        setInterval($.proxy(function(){
	        if(this.$el.width() != dims.w || this.$el.height() != dims.h){
	            this.$el.dialog("option","position", {
                    my: "center bottom",
                    at: "center center"
                });
	            dims.w = this.$el.width();
	            dims.h = this.$el.height();
	        }
	    }, this), 100);
	    $(window).resize($.proxy(function(){
	        this.$el.dialog("option","position", {
                my: "center bottom",
                at: "center center"
            });
	    }, this));
    },
    
    saveAll: function(){
        var copy = this.projects.toArray();
        clearAllMessages();
        _.each(copy, $.proxy(function(project){
            if(_.contains(allowedProjects, project.get('name'))){
                if(project.get('deleted') != "true"){
                    project.save(null, {
                        success: function(){
                            addSuccess("Projects saved");
                        },
                        error: function(){
                            addError("Projects could not be saved");
                        }
                    });
                }
                else {
                    project.destroy(null, {
                        success: function(){
                            addSuccess("Projects saved");
                        },
                        error: function(){
                            addError("Projects could not be saved");
                        }
                    });
                }
            }
        }, this));
    },
    
    addProject: function(){
        var project = _.first(allowedProjects);
        this.projects.add(new PersonProject({name: project, personId: this.person.get('id')}));
    },
    
    addRows: function(){
        if(this.projects.length > 0){
            this.$("#project_rows").empty();
        }
        this.projects.each($.proxy(function(project){
            var view = new ManagePeopleEditProjectsRowView({model: project});
            this.$("#project_rows").append(view.render());
        }, this));
    },
    
    events: {
        "click #add": "addProject"
    },
    
    render: function(){
        this.$el.empty();
        this.$el.html(this.template());
        this.addRows();
        return this.$el;
    }

});

ManagePeopleEditProjectsRowView = Backbone.View.extend({
    
    tagName: 'tr',
    
    initialize: function(){
        this.listenTo(this.model, "change", this.update);
        this.template = _.template($('#edit_projects_row_template').html());
    },
    
    delete: function(){
        this.model.delete = true;
    },
    
    // Sets the end date to infinite (0000-00-00)
    setInfinite: function(){
        this.$("input[name=endDate]").val('0000-00-00');
        this.model.set('endDate', '0000-00-00');
    },
    
    events: {
        "click #infinity": "setInfinite"
    },
    
    update: function(){
        if(this.model.get('deleted') == "true"){
            this.$el.css('background', '#FEB8B8');
        }
        else{
            this.$el.css('background', '#FFFFFF');
        }
    },
   
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        return this.$el;
    }, 
    
});
