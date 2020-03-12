ProductView = Backbone.View.extend({

    initialize: function(){
        this.model.fetch({
            error: function(e){
                this.$el.html("This Product does not exist");
            }.bind(this)
        });
        this.model.bind('change', this.render, this);
        this.template = _.template($('#product_template').html());
    },
    
    events: {
        "click #editProduct": "editProduct",
        "click #deleteProduct": "deleteProduct"
    },
    
    editProduct: function(){
        document.location = document.location + '/edit';
    },
    
    deleteProduct: function(){
        if(this.model.get('deleted') != true){
            if(confirm("Are you sure you want to delete this " + this.model.get('category') + "?")){
                this.model.destroy({
                    success: function(model, response) {
                        if(response.deleted == true){
                            model.set(response);
                            clearSuccess();
                            clearError();
                            addSuccess('The ' + response.category + ' <i>' + response.title + '</i> was deleted sucessfully');
                        }
                        else{
                            clearSuccess();
                            clearError();
                            addError('The ' + response.category + ' <i>' + response.title + '</i> was not deleted sucessfully');
                        }
                    },
                    error: function(model, response) {
                        clearSuccess();
                        clearError();
                        addError('The ' + response.category + ' <i>' + response.title + '</i> was not deleted sucessfully');
                    }
                });
            }
        }
        else{
            clearAllMessages();
            addError('This ' + this.model.get('category') + ' is already deleted');
        }
    },
    
    renderAuthors: function(){
        var views = Array();
        var that = this;
        _.each(this.model.get('authors'), function(author, index){
            var link = new Link({id: author.id,
                                 text: author.name.replace(/&quot;/g, ''),
                                 url: author.url,
                                 target: ''});
            views.push(new PersonLinkView({model: link}).render());
        });
        var csv = new CSVView({el: this.$('#productAuthors'), model: views});
        csv.separator = ', ';
        csv.render();
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
            this.$('#productProjects').empty();
            this.$('#productProjects').append("<ul>");
            _.each(projects, function(project){
                if(project.get('subprojects').length > 0){
                    projects = _.without(projects, project);
                    if(project.get('id') == -1){
                        this.$('#productProjects ul').append("<li id='" + project.get('id') + "'>" + project.get('name') + "</li>");
                    }
                    else{
                        this.$('#productProjects ul').append("<li id='" + project.get('id') + "'><a href='" + project.get('url') + "'>" + project.get('name') + "</a></li>");
                    }
                    var subs = new Array();
                    _.each(project.get('subprojects'), function(sub){
                        if(_.where(projects, {id: sub.id}).length > 0){
                            subs.push("<a href='" + sub.url + "'>" + sub.name + "</a>");
                            projects = _.without(projects, _.findWhere(projects, {id: sub.id}));
                        }
                    });
                    if(subs.length > 0){
                        this.$('#productProjects li#' + project.get('id')).append("&nbsp;<span>(" + subs.join(', ') + ")</span>");
                    }
                }
            });
            _.each(projects, function(project){
                if(project.get('id') == -1){
                    this.$('#productProjects ul').append("<li id='" + project.get('id') + "'>" + project.get('name') + "</li>");
                }
                else{
                    this.$('#productProjects ul').append("<li id='" + project.get('id') + "'><a href='" + project.get('url') + "'>" + project.get('name') + "</a></li>");
                }
            });
        }.bind(this));
    },
    
    render: function(){
        main.set('title', this.model.get('title'));
        $("#pageTitle").css("margin-right", "115px");
        this.$el.empty();
        var data = this.model.toJSON();
        _.extend(data, dateTimeHelpers);
        this.$el.html(this.template(data));
        this.renderAuthors();
        this.renderProjects();
        if(this.model.get('deleted') == true){
            this.$el.find("#deleteProduct").prop('disabled', true);
            clearInfo();
            addInfo('This ' + this.model.get('category') + ' has been deleted, and will not show up anywhere else on the forum.  You may still edit the ' + this.model.get('category') + '.');
        }
        return this.$el;
    }

});
