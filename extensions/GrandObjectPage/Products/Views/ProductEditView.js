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
        /*var formData = this.$("form").serializeArray();
        for(i in formData){
            var field = formData[i];
            console.log(field);
            if(field.name.indexOf('.') == -1){
                this.model.set(field.name, field.value);
            }
            else{
                var index = field.name.indexOf('.');
                var data = this.model.get(field.name.substr(0, index), field.value);
                data[field.name.substr(index + 1)] = field.value;
            }
        }
        console.log(this.model.toJSON());
        */
    },
    
    cancel: function(){
        document.location = this.model.get('url');
    },
    
    renderAuthors: function(){
        var allPeople = new People();
        allPeople.fetch();
        var spin = spinner("productAuthors", 10, 20, 10, 3, '#888');
        allPeople.bind('reset', function(){
            var left = _.pluck(this.model.get('authors'), 'name');
            var right = allPeople.pluck('realname');
            
            var switcheroo = new Switcheroo({name: 'author', 'left': left, 'right': right});
            var switcherooView = new SwitcherooView({el: this.$("#productAuthors"), model: switcheroo});
            switcherooView.render();
        }, this);
    },
    
    renderProjects: function(){
        var allProjects = new Projects();
        var that = this;
        var myProjects;
        var spin = spinner("productSpinner", 10, 20, 10, 3, '#888');
        $.when(allProjects.fetch(), 
               myProjects = me.getProjects()).then(function(){
            current = myProjects.getCurrent();
            myProjects.ready().then(function(){
                that.$("#productSpinner").empty();
                var html = HTML.TagIt(that, 'projects.name', 
                                      {
                                       suggestions: current.pluck('name'),
                                       values: _.pluck(that.model.get('projects'), 'name'),
                                       capitalize: true,
                                       options: {availableTags: allProjects.pluck('name')}
                                      });
                that.$("#productProjects").html(html);
            });
        });    
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

        return this.$el;
    }

});
