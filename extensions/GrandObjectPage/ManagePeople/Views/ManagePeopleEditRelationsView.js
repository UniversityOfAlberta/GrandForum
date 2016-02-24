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
            this.model.ready().then($.proxy(function(){
                this.render();
            }, this));
        }, this));
        
        var dims = {w:0, h:0};
        // Reposition the dialog when the window is resized or the dialog is resized
        this.interval = setInterval($.proxy(function(){
            this.$el.dialog("option","position", {
                my: "center center",
                at: "center center",
                offset: "0 -75%"
            });
            if(this.$el.height() >= $(window).height() - 100){
                this.$el.height($(window).height() - 100);
            }
            else{
                this.$el.height('auto');
            }
	    }, this), 100);
    },
    
    saveAll: function(){
        var copy = this.relations.where({'user2': this.person.get('id')})
        clearAllMessages();
        var requests = new Array();
        _.each(copy, $.proxy(function(relation){
            if(relation.get('deleted') != "true"){
                requests.push(relation.save(null));
            }
            else {
                requests.push(relation.destroy(null));
            }
        }, this));
        $.when.apply($, requests).then(function(){
            addSuccess("Relations saved");
        }).fail(function(){
            addError("Relations could not be saved");
        });
    },
    
    addRelation: function(){
        this.relations.add(new PersonRelation({type: 'Works With', user1: me.get('id'), user2: this.person.get('id')}));
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
    
    initialize: function(){
        this.model.set('deleted', false);
        this.listenTo(this.model, "change", this.update);
        this.listenTo(this.model, "change:projects", this.renderProjects);
        this.template = _.template($('#edit_relations_row_template').html());
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
        var projects = this.model.get('projects');
        if(_.where(projects, {name: name}).length == 0){
            projects.push({id: null, name: name});
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
