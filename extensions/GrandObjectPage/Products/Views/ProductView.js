ProductView = Backbone.View.extend({

    deleteDialog: null,

    initialize: function(){
        this.model.fetch({
            error: $.proxy(function(e){
                this.$el.html("This Product does not exist");
            }, this)
        });
        this.model.bind('change', this.render, this);
        this.template = _.template($('#product_template').html());
    },
    
    events: {
        "click #editProduct": "editProduct",
        "click #deleteProduct": "deleteProduct",
        "click #undeleteProduct": "undeleteProduct"
    },
    
    editProduct: function(){
        document.location = document.location + '/edit';
    },
    
    deleteProduct: function(){
        if(this.model.get('deleted') != true){
	        this.deleteDialog.dialog('open');
        }
        else{
            clearAllMessages();
            addError('This ' + this.model.get('category') + ' is already deleted');
        }
    },
    
    undeleteProduct: function(){
        this.model.set('deleted', 0);
        this.$("#deleteProduct").prop("disabled", true);
        this.$("#undeleteProduct").prop("disabled", true);
        this.model.save(null, {
            success: $.proxy(function(model, response) {
                clearSuccess();
                clearError();
                addSuccess('The ' + response.category + ' <i>' + response.title + '</i> was un-deleted sucessfully');
                this.$("#deleteProduct").prop("disabled", false);
                this.$("#undeleteProduct").prop("disabled", false);
            }, this),
            error: $.proxy(function(model, response) {
                clearSuccess();
                clearError();
                addError('The ' + response.category + ' <i>' + response.title + '</i> was not un-deleted sucessfully');
                this.$("#deleteProduct").prop("disabled", false);
                this.$("#undeleteProduct").prop("disabled", false);
            }, this)
        });
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
        csv.separator = '; ';
        csv.render();
    },
    
    renderContributors: function(){
        var views = Array();
        var that = this;
        _.each(this.model.get('contributors'), function(author, index){
            var link = new Link({id: author.id,
                                 text: author.name.replace(/&quot;/g, ''),
                                 url: author.url,
                                 target: ''});
            views.push(new PersonLinkView({model: link}).render());
        });
        var csv = new CSVView({el: this.$('#productContributors'), model: views});
        csv.separator = '; ';
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
        $.when.apply(null, xhrs).done($.proxy(function(){
            this.$('#productProjects').empty();
            this.$('#productProjects').append("<ul>");
            _.each(projects, function(project){
                if(project.get('subprojects').length > 0){
                    projects = _.without(projects, project);
                    this.$('#productProjects ul').append("<li id='" + project.get('id') + "'><a href='" + project.get('url') + "'>" + project.get('name') + "</a></li>");
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
                this.$('#productProjects ul').append("<li id='" + project.get('id') + "'><a href='" + project.get('url') + "'>" + project.get('name') + "</a></li>");
            });
        }, this));
    },
    
    render: function(){
        main.set('title', this.model.get('title'));
        this.$el.empty();
        var data = this.model.toJSON();
        _.extend(data, dateTimeHelpers);
        this.$el.html(this.template(data));
        this.renderAuthors();
        this.renderContributors();
        this.renderProjects();
        if(this.model.get('deleted') == true){
            this.$("#deleteProduct").prop('disabled', true);
            clearInfo();
            addInfo('This ' + this.model.get('category') + ' has been deleted, and will not show up anywhere else on the ' + siteName + '.  You may still edit the ' + this.model.get('category') + '.');
        }
        this.deleteDialog = this.$("#deleteDialog").dialog({
	            autoOpen: false,
	            modal: true,
	            show: 'fade',
	            resizable: false,
	            draggable: false,
	            open: $.proxy(function(){
	                $("html").css("overflow", "hidden");
	                $(".ui-dialog-buttonpane button:contains('Yes')", this.deleteDialog.parent()).prop("disabled", true);
	                $("#deleteCheck", this.deleteDialog).prop("checked", false);
	                $("#deleteCheck", this.deleteDialog).change($.proxy(function(e){
	                    var isChecked = $(e.currentTarget).is(":checked");
	                    if(isChecked){
	                        $(".ui-dialog-buttonpane button:contains('Yes')", this.deleteDialog.parent()).prop("disabled", false);
	                    }
	                    else{
	                        $(".ui-dialog-buttonpane button:contains('Yes')", this.deleteDialog.parent()).prop("disabled", true);
	                    }
	                }, this));
	            }, this),
	            beforeClose: function(){
	                $("html").css("overflow", "auto");
	            },
	            buttons: {
	                "Yes": $.proxy(function(){
	                    var model = this.model;
	                    if(model.get('deleted') != true){
	                        $("div.throbber", this.deleteDialog).show();
                            model.destroy({
                                success: $.proxy(function(model, response) {
                                    this.deleteDialog.dialog('close');
                                    $("div.throbber", this.deleteDialog).hide();
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
                                }, this),
                                error: function(model, response) {
                                    clearSuccess();
                                    clearError();
                                    addError('The ' + response.category + ' <i>' + response.title + '</i> was not deleted sucessfully');
                                }
                            });
                        }
                        else{
                            this.deleteDialog.dialog('close');
                            clearAllMessages();
                            addError('This ' + model.get('category') + ' is already deleted');
                        }
	                }, this),
	                "No": $.proxy(function(){
	                    this.deleteDialog.dialog('close');
	                }, this)
	            }
	        });
        return this.$el;
    }

});
