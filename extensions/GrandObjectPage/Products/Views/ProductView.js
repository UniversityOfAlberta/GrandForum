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
        if(this.model.get('deleted') != 1){
            this.model.destroy({
                success: function(model, response) {
                    if(response.deleted == '1'){
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
    
    renderData: function(){
        var dataTag = this.$el.find('#productData');
        _.each(this.model.get('data'), function(value, label){
            if(value.trim() != ''){
                var label = label.replace('_', ' ').toTitleCase();
                var data = {'label': label,
                            'value': value};
                dataTag.append(new ProductDataRowView({model:data}).render());
            }
        });
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
        this.$el.html(this.template(this.model.toJSON()));
        this.renderAuthors();
        this.renderData();
        this.renderProjects();
        if(this.model.get('deleted') == '1'){
            this.$el.find("#deleteProduct").prop('disabled', true);
            this.$el.find("#editProduct").prop('disabled', true);
            clearInfo();
            addInfo('This ' + this.model.get('category') + ' has been deleted, and will not show up anywhere else on the forum');
        }
        return this.el;
    }

});

ProductDataRowView = Backbone.View.extend({
    
    tagName: "tr",
    
    initialize: function(){
        this.template = _.template($('#product_data_row_template').html());
    }, 
    
    render: function(){
        this.$el.html(this.template(this.model));
        return this.el;
    }
    
});
