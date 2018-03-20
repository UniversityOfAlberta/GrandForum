ProjectSelectorView = Backbone.View.extend({

    projects: new Projects(),
    allProjects: null,
    otherProjects: null,
    oldProjects: null,

    initialize: function(options){
        this.template = _.template($('#project_selector_template').html());
        this.otherPopupTemplate = _.template($('#other_popup_template').html());
        this.projectsPopupTemplate = _.template($('#projects_popup_template').html());
        
        this.allProjects = new Projects();
        this.allProjects.fetch();
        me.getProjects();
        
        me.projects.ready().then($.proxy(function(){
            this.listenTo(this.model, "change:projects", this.render);
            this.projects = me.projects.getCurrent();
            this.allProjects.ready().then($.proxy(function(){
                var other = new Project({id: "-1", name: "Other"});
                other.id = "-1";
                this.otherProjects = new Projects(this.allProjects.getCurrent().where({status: 'Active'}));
                this.otherProjects.add(other);
                this.oldProjects = this.allProjects.getOld();
                this.otherProjects.remove(this.projects.models);
                this.oldProjects.remove(this.projects.models);
                this.render();
            }, this));
        }, this));
        
        $(document).click($.proxy(function(e){
            var popup = $("div.popupBox:visible").not(":animated").first();
            if(popup.length > 0 && !$.contains(popup[0], e.target)){
                this.model.trigger("change:projects");
            }
        }, this));
    },
    
    projectChecked: function(projectId){
        if(_.where(this.model.get('projects'), {id: projectId}).length > 0){
            return 'checked="checked"';
        }
        return "";
    },

    otherChecked: function(){
        var ret = '';
        var projects = this.projects.models;
        var allProjects = new Array();
        _.each(projects, function(proj){
            allProjects.push(proj);
            _.each(proj.get('subprojects'), function(sub){
                allProjects.push(sub);
            });
        });
        _.each(this.model.get('projects'), function(proj){
            if(_.where(allProjects, {id: proj.id}).length == 0){
                ret = 'checked="checked"';
            }
        });
        return ret;
    },
    
    events: {
    
    },
    
    render: function(){
        view = this;
        this.$el.html(this.template(this.model.toJSON()));
        return this.$el;
    }

});
