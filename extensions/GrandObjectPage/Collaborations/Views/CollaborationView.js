CollaborationView = Backbone.View.extend({
    mention: null,
    searchTerm: null,
    products: null,
    tags: null,

    initialize: function(){
        this.mention = new Array();
        this.products = new Array();
        this.tags = new Array();
        this.model.fetch({
            error: $.proxy(function(e){
                this.$el.html("This Collaboration does not exist");
            }, this)
        });
        this.model.bind('change', this.render, this);
        this.template = _.template($('#collaboration_template').html());
    },
    
    editCollaboration: function(){
        document.location = this.model.get('url') + "/edit";
    },

    delete: function(e) {
        if (confirm("Are you sure you want to delete this collaboration?")) {
            this.model.destroy({success: function() {
                document.location = wgServer + wgScriptPath + "/index.php/Special:CollaborationPage#";
                _.defer(function() {
                    clearAllMessages();
                    addSuccess("Collaboration deleted")
                });
            }, error: function() {
                clearAllMessages();
                addError("Collaboration failed");
            }});
        }
    },
    
    events: {
        "click #editCollaboration": "editCollaboration",
        "click #deleteCollaboration": "delete",
        "click #exportBib": "exportCollaboration",
    },


    search: function() {
        var searchTerm = this.$("#search").val();
        if (searchTerm == "") {
            return;
        }
        var lis = this.$("#products li");
        _.each(this.products, function(prod, index){
            var v = $(lis.get(index));
            if (v.css('display') != "none") {
                var pub = prod.get("citation").replace(/<\/?(.|\n)*?>/g, "");
                var tags = prod.get("tags").join(", ");
                pub = pub.replace(/&nbsp;/g, " ").toLowerCase() + tags;

                if (pub.indexOf(searchTerm.toLowerCase()) != -1) {
                    $(lis.get(index)).show();
                } else {
                    $(lis.get(index)).hide();
                }   
            }
        });

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
        $.when.apply(null, xhrs).done($.proxy(function(){
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
        }, this));
    },
    
    render: function(){
        main.set('title', this.model.get('title'));
        
        var formType = this.model.getType();
        if(this.model.isNew()){
            main.set('title', 'New ' + formType);
        }
        else {
            main.set('title', 'Edit ' + formType);
        }
        this.renderProjects();
        this.$el.html(this.template(_.extend({formType:formType}, this.model.toJSON())));
        return this.$el;
    }

});
