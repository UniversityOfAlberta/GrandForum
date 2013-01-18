ProductView = Backbone.View.extend({

    initialize: function(){
        this.model.fetch();
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
        console.log(this.model.toJSON());
        if(this.model.get('deleted') != true){
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
        else{
            clearAllMessages();
            addError('This ' + this.model.get('category') + ' is already deleted');
        }
    },
    
    renderAuthors: function(){
        var views = Array();
        _.each(this.model.get('authors'), function(author, index){
            var link = new Link({id: author.id,
                                 text: author.name,
                                 url: author.url,
                                 target: '_blank'});
            views.push(new PersonLinkView({model: link}).render());
        });
        csv = new CSVView({el: this.$el.find('#productAuthors'), model: views}).render();
    },
    
    renderProjects: function(){
        var views = Array();
        _.each(this.model.get('projects'), function(project, index){
            var link = new Link({id: project.id,
                                 text: project.name,
                                 url: project.url,
                                 target: '_blank'});
            views.push(new ProjectLinkView({model: link}).render());
        });
        csv = new CSVView({el: this.$el.find('#productProjects'), model: views}).render();
    },
    
    render: function(){
        main.set('title', this.model.get('title'));
        this.$el.empty();
        var data = this.model.toJSON();
        _.extend(data, dateTimeHelpers);
        this.$el.html(this.template(data));
        this.renderAuthors();
        this.renderProjects();
        if(this.model.get('deleted') == true){
            this.$el.find("#deleteProduct").prop('disabled', true);
            this.$el.find("#editProduct").prop('disabled', true);
            clearInfo();
            addInfo('This ' + this.model.get('category') + ' has been deleted, and will not show up anywhere else on the forum');
        }
        return this.el;
    }

});
