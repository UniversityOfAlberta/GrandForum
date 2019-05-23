ManagePeopleEditProjectLeadersView = Backbone.View.extend({

    projects: null,
    person: null,
    projectViews: null,
    interval: null,

    initialize: function(options){
        this.person = options.person;
        this.model.fetch();
        this.projectViews = new Array();
        this.template = _.template($('#edit_projects_template').html());
        this.person.getRoles();
        this.model.ready().then(function(){
            this.projects = this.model;
            this.listenTo(this.projects, "add", this.addRows);
            this.projects.each(function(p){
                p.startTracking();
            });
            this.render();
        }.bind(this));
        // Reposition the dialog when the window is resized or the dialog is resized
        var dim = {w1: 0,
                   h1: 0,
                   w2: 0,
                   h2: 0};
        this.interval = setInterval(function(){
            if(this.$el.width() != dim.w1 ||
               this.$el.height() != dim.h1 ||
               $(window).width() != dim.w2 ||
               $(window).height() != dim.h2){
                if(this.$el.height() >= $(window).height() - 100){
                    this.$el.height($(window).height() - 100);
                }
                else{
                    this.$el.height('auto');
                }
                this.$el.dialog("option","position", {
                    my: "center center",
                    at: "center center"
                });
            }
            dim.w1 = this.$el.width();
            dim.h1 = this.$el.height();
            dim.w2 = $(window).width();
            dim.h2 = $(window).height();
	    }.bind(this), 100);
    },
    
    saveAll: function(){
        var copy = this.projects.toArray();
        clearAllMessages();
        var requests = new Array();
        _.each(copy, function(project){
            if(_.contains(allowedProjects, project.get('name')) && project.unsavedAttributes() != false){
                if(project.get('deleted') != "true"){
                    requests.push(project.save(null));
                }
                else {
                    requests.push(project.destroy(null));
                }
            }
        }.bind(this));
        $.when.apply($, requests).done(function(){
            addSuccess("Projects saved");
        }).fail(function(){
            addError("Projects could not be saved");
        });
    },
    
    addProject: function(){
        var project = _.first(allowedProjects);
        var personLeadership = new PersonLeadership();
        personLeadership.startTracking();
        personLeadership.set("name", project);
        personLeadership.set("personId", this.person.get('id'));
        this.projects.add(personLeadership);
        this.$el.scrollTop(this.el.scrollHeight);
    },
    
    addRows: function(){
        this.projects.each(function(project, i){
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
        }.bind(this));
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
