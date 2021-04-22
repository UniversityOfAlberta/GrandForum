ProjectSelectorView = Backbone.View.extend({

    projects: null,
    allProjects: null,
    otherProjects: null,
    oldProjects: null,
    otherOnly: false,
    inlineOther: false,
    template: _.template($('#project_selector_template').html()),
    otherPopupTemplate: _.template($('#other_popup_template').html()),
    projectsPopupTemplate: _.template($('#projects_popup_template').html()),

    initialize: function(options){
        this.otherOnly = (options.otherOnly != undefined) ? options.otherOnly : false;
        this.inlineOther = (options.inlineOther != undefined) ? options.inlineOther : false;
        
        this.listenTo(Backbone, 'document-click-event', function(e){
            // Clicking somewhere else in the document, close popup
            var popup = this.$("div.popupBox:visible").not(":animated").first();
            if(popup.length > 0 && !$.contains(popup[0], e.target)){
                this.model.trigger("change:projects");
            }
        }.bind(this));
        
        if(options.allProjects != undefined &&
           options.projects    != undefined &&
           options.otherProjects != undefined &&
           options.oldProjects != undefined){
            // If everything was already set, just go straight into rendering, no need for callbacks
            this.allProjects = options.allProjects;
            this.projects = options.projects;
            this.otherProjects = options.otherProjects;
            this.oldProjects = options.oldProjects;
            this.listenTo(this.model, "change:projects", this.render);
            this.render();
            return;
        }
        
        if(options.allProjects != undefined){
            this.allProjects = options.allProjects;
        }
        else{
            this.allProjects = new Projects();
            this.allProjects.fetch();
            me.getProjects();
        }
        
        me.projects.ready().then(function(){
            this.listenTo(this.model, "change:projects", this.render);
            if(options.projects != undefined){
                this.projects = options.projects;
            }
            else{
                this.projects = me.projects.getCurrent();
            }
            this.allProjects.ready().then(function(){
                if(options.otherProjects != undefined && options.oldProjects != undefined){
                    this.otherProjects = options.otherProjects;
                    this.oldProjects = options.oldProjects;
                }
                else{ 
                    var other = new Project({id: "-1", name: "Other"});
                    other.id = "-1";
                    this.otherProjects = new Projects(this.allProjects.getCurrent().where({status: 'Active'}));
                    this.otherProjects.add(other);
                    this.oldProjects = this.allProjects.getOld();
                    this.otherProjects.remove(this.projects.models);
                    this.oldProjects.remove(this.projects.models);
                }
                this.render();
            }.bind(this));
        }.bind(this));
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
    
    select: function(projectId){
        var projects = this.model.get('projects');
        if(_.where(projects, {id: projectId}).length == 0){
            projects.push({id: projectId});
        }
        // Only trigger an event if this is a parent
        if(this.$("input[data-project=" + projectId + "]").attr('name') == 'project'){
            this.model.trigger("change:projects");
        }
    },
    
    unselect: function(projectId){
        var project = _.findWhere(this.projects.models
                                      .concat(this.otherProjects.models)
                                      .concat(this.oldProjects.models), {id: projectId});
        var projects = this.model.get('projects');

        // Unselect all subprojects as well
        if(project != undefined){
            _.each(project.get('subprojects'), function(sub){
                var index = _.indexOf(projects, _.findWhere(projects, {id: sub.id}));
                if(index != -1){
                    projects.splice(index, 1);
                    this.$("input[data-project=" + sub.id + "]").prop('checked', false);
                }
            }.bind(this));
        }
        var index = _.indexOf(projects, _.findWhere(projects, {id: projectId}));
        if(index != -1){
            projects.splice(index, 1);
            // Only trigger an event if this is a parent
            if(this.$("input[data-project=" + projectId + "]").attr('name') == 'project'){
                this.model.trigger("change:projects");
            }
        }
    },
    
    toggleSelect: function(e){
        var target = $(e.currentTarget);
        var projectId = target.attr('data-project');
        if(target.is(":checked")){
            // 'Check' Project
            this.select(projectId);
            if(target.attr('name') == "project"){
                //this.$("div[data-project=" + projectId + "] div.subprojectPopup").slideDown();
            }
            else if(target.attr('name') == "subproject"){
                var parentId = target.attr('data-parent');
                this.$("div[data-project=" + parentId + "] div.subprojectPopup").show();
            }
            else if(target.attr('name') == "otherproject"){
                $("div.otherSubProjects", target.parent()).slideDown();
            }
        }
        else{
            // 'Uncheck' Project
            this.unselect(projectId);
            if(target.attr('name') == "project"){
                // Do nothing
            }
            else if(target.attr('name') == "subproject"){
                var parentId = target.attr('data-parent');
                this.$("div[data-project=" + parentId + "] div.subprojectPopup").show();
            }
            else if(target.attr('name') == "otherproject"){
                $("div.otherSubProjects", target.parent()).slideUp();
            }
        }
    },
    
    filterSearch: function(e){
        var target = $(e.currentTarget);
        var value = target.val();
        var block = target.parent();
        var options = $("div.popupMainProject", block);
        options.each(function(i, el){
            var text = $(el).text();
            if(unaccentChars(text).indexOf(unaccentChars(value)) == -1){
                $(el).slideUp(150);
            }
            else{
                $(el).slideDown(150);
            }
        });
    },
    
    showOther: function(e){
        this.$("div.otherPopup").html(this.otherPopupTemplate(this.model.toJSON()));
        var lastHeight = this.$el.prop("scrollHeight")
        var interval = setInterval(function(){
            if(this.$el.prop("scrollHeight") > lastHeight){
                this.$el.scrollTop(this.$el.scrollTop() + Math.abs(lastHeight - this.$el.prop("scrollHeight")));
                lastHeight = this.$el.prop("scrollHeight");
            }
        }.bind(this), 16);
        this.$("div.otherPopup").slideDown(function(){
            clearInterval(interval);
            if(this.$el.prop("scrollHeight") > lastHeight){
                this.$el.scrollTop(this.$el.scrollTop() + Math.abs(lastHeight - this.$el.prop("scrollHeight")));
                lastHeight = this.$el.prop("scrollHeight");
            }
        }.bind(this));
    },
    
    showSubprojects: function(e){
        var target = $(e.currentTarget);
        var projectId = target.attr('data-project');
        var project = _.findWhere(this.projects.models, {id: projectId});
        this.$("div[data-project=" + projectId + "] div.subprojectPopup").html(this.projectsPopupTemplate(_.extend(project.toJSON(), {projects: this.model.get('projects')})));
        this.$("div[data-project=" + projectId + "] div.subprojectPopup").slideDown();
    },
    
    events: {
        "click div.showOther": "showOther",
        "click div.showSubprojects": "showSubprojects",
        "change input.popupBlockSearch": "filterSearch",
        "keyup input.popupBlockSearch": "filterSearch",
        "change input[type=checkbox]": "toggleSelect"
    },
    
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        if(this.inlineOther){
            this.showOther();
        }
        return this.$el;
    }

});
