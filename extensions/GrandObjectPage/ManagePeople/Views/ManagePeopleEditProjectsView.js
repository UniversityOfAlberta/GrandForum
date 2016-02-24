ManagePeopleEditProjectsView = Backbone.View.extend({

    projects: null,
    person: null,
    projectViews: null,

    initialize: function(options){
        this.person = options.person;
        this.model.fetch();
        this.projectViews = new Array();
        this.template = _.template($('#edit_projects_template').html());
        this.person.getRoles();
        this.person.getUniversities();
        this.model.ready().then($.proxy(function(){
            this.projects = this.model;
            this.listenTo(this.projects, "add", this.addRows);
            return this.person.roles;
        }, this)).then($.proxy(function(){
            return this.person.universities;
        }, this)).then($.proxy(function(){
            this.render();
        }, this));
        var dims = {w:0, h:0};
        // Reposition the dialog when the window is resized or the dialog is resized
        setInterval($.proxy(function(){
	        if(this.$el.width() != dims.w || this.$el.height() != dims.h){
	            this.$el.dialog("option","position", {
                    my: "center center",
                    at: "center center",
                    offset: "0 -75%"
                });
	            dims.w = this.$el.width();
	            dims.h = this.$el.height();
	        }
	    }, this), 100);
	    $(window).resize($.proxy(function(){
	        this.$el.dialog("option","position", {
                my: "center center",
                at: "center center",
                offset: "0 -75%"
            });
	    }, this));
    },
    
    saveAll: function(){
        var copy = this.projects.toArray();
        clearAllMessages();
        var requests = new Array();
        _.each(copy, $.proxy(function(project){
            if(_.contains(allowedProjects, project.get('name'))){
                if(project.get('deleted') != "true"){
                    requests.push(project.save(null));
                }
                else {
                    requests.push(project.destroy(null));
                }
            }
        }, this));
        $.when.apply($, requests).done(function(){
            addSuccess("Projects saved");
        }).fail(function(){
            addError("Projects could not be saved");
        });
    },
    
    addProject: function(){
        var project = _.first(allowedProjects);
        this.projects.add(new PersonProject({name: project, personId: this.person.get('id')}));
        this.$el.scrollTop(this.el.scrollHeight);
    },
    
    addRows: function(){
        this.projects.each($.proxy(function(project, i){
            if(this.projectViews[i] == null){
                var view = new ManagePeopleEditProjectsRowView({model: project});
                this.$("#project_rows").append(view.render());
                if(i % 2 == 0){
                    view.$el.addClass('even');
                }
                else{
                    view.$el.addClass('odd');
                }
                this.projectViews[i] = view;
            }
        }, this));
    },
    
    showCard: function(){
        
        var card = new LargePersonCardView({el: this.$("#card"), model: this.person});
        card.render();
        this.$("#accordion").accordion();
    },
       
    render: function(){
        this.$el.empty();
        this.$el.html(this.template());
        this.addRows();
        this.showCard();
        return this.$el;
    }

});

ManagePeopleEditProjectsRowView = Backbone.View.extend({
    
    tagName: 'tr',
    
    initialize: function(){
        this.model.set('deleted', false);
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
            this.$el.addClass('deleted');
        }
        else{
            this.$el.removeClass('deleted');
        }
    },
   
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        this.update();
        return this.$el;
    }, 
    
});
