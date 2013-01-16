ProductEditView = Backbone.View.extend({

    initialize: function(){
        this.model.fetch();
        this.model.bind('change', this.render, this);
        this.template = _.template($('#product_edit_template').html());
    },
    
    events: {
        "click #saveProduct": "saveProduct",
        "click #cancel": "cancel"
    },
    
    saveProduct: function(){
        document.location = this.model.get('url');
    },
    
    cancel: function(){
        document.location = this.model.get('url');
    },
    
    renderAuthors: function(){
        var views = Array();
        var allPeople = new People();
        allPeople.fetch();
        spin = spinner("productAuthors", 10, 20, 10, 3, '#888');
        allPeople.bind('reset', function(){
            var left = _.pluck(this.model.get('authors'), 'name');
            var right = allPeople.pluck('realname');
            
            var switcheroo = new Switcheroo({name: 'author', 'left': left, 'right': right});
            var switcherooView = new SwitcherooView({el: this.$("#productAuthors"), model: switcheroo});
            switcherooView.render();
        }, this);
    },
    
    renderData: function(){
        var dataTag = this.$el.find('#productData');
        _.each(this.model.get('data'), function(value, label){
            if(value.trim() != ''){
                var label = label.replace('_', ' ').toTitleCase();
                var data = {'label': label,
                            'value': value};
                dataTag.append(new ProductEditDataRowView({model:data}).render());
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
        var data = this.model.toJSON();
        _.extend(data, dateTimeHelpers);
        this.$el.html(this.template(data));
        this.renderAuthors();
        this.renderData();
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

ProductEditDataRowView = Backbone.View.extend({
    
    tagName: "tr",
    
    initialize: function(){
        this.template = _.template($('#product_edit_data_row_template').html());
    }, 
    
    render: function(){
        this.$el.html(this.template(this.model));
        return this.el;
    }
    
});
