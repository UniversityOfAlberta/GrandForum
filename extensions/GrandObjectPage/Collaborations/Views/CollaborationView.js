CollaborationView = Backbone.View.extend({

    initialize: function(){
        this.model.fetch({
            error: function(e){
                this.$el.html("This Collaboration does not exist");
            }.bind(this)
        });
        this.model.bind('change', this.render, this);
        this.template = _.template($('#collaboration_template').html());
    },
    
    editCollaboration: function(){
        document.location = this.model.get('url') + "/edit";
    },

    delete: function(e) {
        var type = this.model.getType();
        if (confirm("Are you sure you want to delete this " + type.toLowerCase() + "?")) {
            this.model.destroy({success: function() {
                document.location = wgServer + wgScriptPath + "/index.php/Special:CollaborationPage#";
                _.defer(function() {
                    clearAllMessages();
                    addSuccess(type + " deleted")
                });
            }, error: function() {
                clearAllMessages();
                addError(type + " deletion failed");
            }});
        }
    },
    
    events: {
        "click #editCollaboration": "editCollaboration",
        "click #deleteCollaboration": "delete",
        "click #exportBib": "exportCollaboration",
    },

    unique: function (array) {
        return $.grep(array, function(el, index) {
            return index === $.inArray(el, array);
        }).sort();
    },

    renderProjects: function(){
        var xhrs = new Array();
        var projects = new Array();
        _.each(this.model.get('projects'), function(proj){
            var project = new Project({id: proj.id});
            projects.push(project);
            xhrs.push(project.fetch());
        });
        $.when.apply(null, xhrs).done(function(){
            this.$('#collaborationProjects').empty();
            this.$('#collaborationProjects').append("<ul>");
            _.each(projects, function(project){
                if(project.get('subprojects').length > 0){
                    projects = _.without(projects, project);
                    if(project.get('id') == -1){
                        this.$('#collaborationProjects ul').append("<li id='" + project.get('id') + "'>" + project.get('name') + "</li>");
                    }
                    else{
                        this.$('#collaborationProjects ul').append("<li id='" + project.get('id') + "'><a href='" + project.get('url') + "'>" + project.get('name') + "</a></li>");
                    }
                    var subs = new Array();
                    _.each(project.get('subprojects'), function(sub){
                        if(_.where(projects, {id: sub.id}).length > 0){
                            subs.push("<a href='" + sub.url + "'>" + sub.name + "</a>");
                            projects = _.without(projects, _.findWhere(projects, {id: sub.id}));
                        }
                    });
                    if(subs.length > 0){
                        this.$('#collaborationProjects li#' + project.get('id')).append("&nbsp;<span>(" + subs.join(', ') + ")</span>");
                    }
                }
            });
            _.each(projects, function(project){
                if(project.get('id') == -1){
                    this.$('#collaborationProjects ul').append("<li id='" + project.get('id') + "'>" + project.get('name') + "</li>");
                }
                else{
                    this.$('#collaborationProjects ul').append("<li id='" + project.get('id') + "'><a href='" + project.get('url') + "'>" + project.get('name') + "</a></li>");
                }
            });
            if (projects.length == 0) {
                this.$("#collaborationProjects").append("<span class='empty_box_content'>No projects</span>");
            }
        }.bind(this));
    },
    
    render: function(){
        main.set('title', this.model.get('title'));
        var formType = this.model.getType();
        this.$el.html(this.template(_.extend({formType:formType}, this.model.toJSON())));
        this.renderProjects();
        this.$("#fields").parent(".collab_box").css("max-width", Math.max(625, this.$("#fields").outerWidth()));
        return this.$el;
    }

});
