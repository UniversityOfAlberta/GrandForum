ManagePeopleEditRolesView = Backbone.View.extend({

    roles: null,
    person: null,
    roleViews: null,
    interval: null,

    initialize: function(options){
        this.person = options.person;
        this.model.fetch();
        this.roleViews = new Array();
        this.listenTo(this.model, "change", this.render);
        this.template = _.template($('#edit_roles_template').html());
        this.model.ready().then(function(){
            this.roles = this.model.getAll();
            this.listenTo(this.roles, "add", this.addRows);
            this.model.ready().then(function(){
                this.roles.each(function(r){
                    r.startTracking();
                });
                this.render();
            }.bind(this));
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
        var copy = this.roles.toArray();
        clearAllMessages();
        var requests = new Array();
        _.each(copy, function(role){
            if(_.contains(allowedRoles, role.get('name')) && role.unsavedAttributes() != false){
                if(role.get('deleted') != "true"){
                    requests.push(role.save(null));
                }
                else {
                    requests.push(role.destroy(null));
                }
            }
        }.bind(this));
        $.when.apply($, requests).then(function(){
            addSuccess("Roles saved");
        }).fail(function(){
            addError("Roles could not be saved");
        });
    },
    
    addRole: function(){
        var role = new Role();
        role.startTracking();
        role.set("name", "HQP");
        role.set("userId", this.person.get('id'));
        this.roles.add(role);
        this.$el.scrollTop(this.el.scrollHeight);
    },
    
    addRows: function(){
        this.roles.each(function(role, i){
            if(this.roleViews[i] == null){
                var view = new ManagePeopleEditRolesRowView({person: this.person, model: role});
                this.$("#role_rows").append(view.render());
                if(i % 2 == 0){
                    view.$el.addClass('even');
                }
                else{
                    view.$el.addClass('odd');
                }
                this.roleViews[i] = view;
            }
        }.bind(this));
    },
    
    render: function(){
        this.$el.empty();
        this.$el.html(this.template());
        this.addRows();
        return this.$el;
    }

});

ManagePeopleEditRolesRowView = Backbone.View.extend({
    
    tagName: 'tr',
    person: null,
    
    initialize: function(options){
        this.person = options.person;
        this.listenTo(this.model, "change", this.update);
        this.listenTo(this.model, "change:projects", this.renderProjects);
        this.template = _.template($('#edit_roles_row_template').html());
    },
    
    delete: function(){
        this.model.delete = true;
    },
    
    // Sets the end date to infinite (0000-00-00)
    setInfinite: function(){
        this.$("input[name=endDate]").val('0000-00-00');
        this.model.set('endDate', '0000-00-00');
    },
    
    addProject: function(event){
        var selectedProject = this.$("#selectedProject option:selected");
        var name = selectedProject.text();
        var projects = this.model.get('projects').slice();
        if(name != '---' && _.where(projects, {name: name}).length == 0){
            projects.push({id: null, name: name});
            this.model.set('projects', projects);
            this.model.trigger('change:projects');
        }
    },
    
    deleteProject: function(event){
        var el = $(event.currentTarget);
        var projects = _.filter(this.model.get('projects'), function(project){
            return project.name != el.attr('data-project-id');
        });
        this.model.set('projects', projects);
    },
    
    events: {
        "click #infinity": "setInfinite",
        "click .roleProject": "deleteProject",
        "click #addProject": "addProject",
        "change #selectedProject": "addProject"
    },
    
    update: function(){
        if(this.model.get('deleted') == "true"){
            this.$el.addClass('deleted');
        }
        else{
            this.$el.removeClass('deleted');
        }
        if((this.model.get('name') == PL || 
            this.model.get('name') == APL || 
            this.model.get('name') == PS || 
            this.model.get('name') == PA) && 
           this.model.get('projects').length == 0){
            this.$(".projectsCell").css("background", "#FDEEB2")
                                   .css("box-shadow", "inset 0 0 0 1px #9C600D");
            this.$(".projError").text("There should be a project associated with this role").show();
        }
        else{
            this.$(".projectsCell").css("background", "")
                                   .css("box-shadow", "");
            this.$(".projError").text("").hide();
        }
    },
    
    renderProjects: function(){
        this.$("#projects").empty();
        var template = _.template($("#edit_role_projects_template").html());
        _.each(this.model.get('projects'), function(proj){
            this.$("#projects").append(template(proj));
        }.bind(this));
        if(this.$("#projects tr").length == 0){
            this.$("#projects").append("<tr><td align='center' colspan='2'>No Projects</td></tr>");
        }
    },
   
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        this.renderProjects();
        this.update();
        return this.$el;
    }, 
    
});
