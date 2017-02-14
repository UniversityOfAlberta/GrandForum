ManagePeopleEditRelationsView = Backbone.View.extend({

    relations: null,
    person: null,
    relationViews: null,
    interval: null,

    initialize: function(options){
        this.person = options.person;
        this.model.fetch();
        this.relationViews = new Array();
        this.template = _.template($('#edit_relations_template').html());
        this.model.ready().then($.proxy(function(){
            this.relations = this.model;
            this.listenTo(this.relations, "add", this.addRows);
            this.relations.each(function(r){
                r.startTracking();
            });
            this.render();
        }, this));
        
        var dims = {w:0, h:0};
        // Reposition the dialog when the window is resized or the dialog is resized
        var dim = {w1: 0,
                   h1: 0,
                   w2: 0,
                   h2: 0};
        this.interval = setInterval($.proxy(function(){
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
	    }, this), 100);
    },
    
    saveAll: function(){
        var copy = this.relations.where({'user2': this.person.get('id')});
        clearAllMessages();
        var requests = new Array();
        _.each(copy, $.proxy(function(relation){
            if(relation.unsavedAttributes() != false){
                if(relation.get('deleted') != "true"){
                    requests.push(relation.save(null));
                }
                else {
                    requests.push(relation.destroy(null));
                }
            }
        }, this));
        $.when.apply($, requests).then(function(){
            addSuccess("Relations saved");
        }).fail(function(){
            addError("Relations could not be saved");
        });
    },
    
    addRelation: function(){
        var relation = new PersonRelation();
        relation.startTracking();
        relation.set("type", _.first(relationTypes));
        relation.set("user1", me.get('id'));
        relation.set("user2", this.person.get('id'));
        this.relations.add(relation);
        this.$el.scrollTop(this.el.scrollHeight);
    },
    
    addRows: function(){
        var relations = new Backbone.Collection(this.relations.where({'user2': this.person.get('id')}));
        relations.each($.proxy(function(relation, i){
            if(this.relationViews[i] == null){
                var view = new ManagePeopleEditRelationsRowView({model: relation});
                this.$("#relation_rows").append(view.render());
                if(i % 2 == 0){
                    view.$el.addClass('even');
                }
                else{
                    view.$el.addClass('odd');
                }
                this.relationViews[i] = view;
            }
        }, this));
    },
    
    render: function(){
        this.$el.empty();
        this.$el.html(this.template());
        this.addRows();
        return this.$el;
    }

});

ManagePeopleEditRelationsRowView = Backbone.View.extend({
    
    tagName: 'tr',
    owner: null,
    target: null,
    
    initialize: function(){
        this.model.set('deleted', false);
        this.owner = this.model.getOwner();
        this.target = this.model.getTarget();
        
        this.listenTo(this.model, "change", this.update);
        this.listenTo(this.model, "change:projects", this.renderProjects);
        this.listenTo(this.owner, "sync", this.render);
        this.listenTo(this.target, "sync", this.render);
        
        this.owner.fetch();
        this.target.fetch();

        this.template = _.template($('#edit_relations_row_template').html());
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
        if(_.where(projects, {name: name}).length == 0){
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
        "click #addProject": "addProject"
    },
    
    update: function(){
        if(this.model.get('deleted') == "true"){
            this.$el.addClass('deleted');
        }
        else{
            this.$el.removeClass('deleted');
        }
    },
    
    renderProjects: function(){
        this.$("#projects").empty();
        var template = _.template($("#edit_role_projects_template").html());
        _.each(this.model.get('projects'), $.proxy(function(proj){
            this.$("#projects").append(template(proj));
        }, this));
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
