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
        this.$(".throbber").show();
        this.$("#saveProduct").prop('disabled', true);
        this.model.save(null, {
            success: $.proxy(function(){
                this.$(".throbber").hide();
                this.$("#saveProduct").prop('disabled', false);
                clearAllMessages();
                document.location = this.model.get('url');
            }, this),
            error: $.proxy(function(){
                this.$(".throbber").hide();
                this.$("#saveProduct").prop('disabled', false);
                clearAllMessages();
                addFailure("There was a problem saving the Product");
            }, this)
        });
    },
    
    cancel: function(){
        document.location = this.model.get('url');
    },
    
    renderAuthorsWidget: function(){
        var left = _.pluck(this.model.get('authors'), 'name');
        var right = this.allPeople.pluck('realName');
        var html = HTML.Switcheroo(this, 'authors.name', {name: 'author',
                                                          'left': left,
                                                          'right': right
                                                          });
        this.$("#productAuthors").html(html);
        createSwitcheroos();
    },
    
    renderAuthors: function(){
        if(this.allPeople != null){
            this.renderAuthorsWidget();
        }
        else{
            this.allPeople = new People();
            allPeople = this.allPeople;
            this.allPeople.fetch();
            var spin = spinner("productAuthors", 10, 20, 10, 3, '#888');
            this.allPeople.bind('sync', function(){
                this.renderAuthorsWidget();
            }, this);
        }
    },
    
    renderProjectsWidget: function(){
        this.$("#productSpinner").empty();
        var html = HTML.TagIt(this, 'projects.name', 
                              {
                               suggestions: this.current.pluck('name'),
                               values: _.pluck(this.model.get('projects'), 'name'),
                               capitalize: false,
                               options: {availableTags: this.allProjects.pluck('name')}
                              });
        this.$("#productProjects").html(html);
    },
    
    renderProjects: function(){
        if(this.allProjects != null){
            this.renderProjectsWidget();
        }
        else{
            this.allProjects = new Projects();
            var that = this;
            var myProjects;
            var spin = spinner("productSpinner", 10, 20, 10, 3, '#888');
            $.when(that.allProjects.fetch(), 
                   that.myProjects = me.getProjects()).then(function(){
                that.current = that.myProjects.getCurrent();
                that.myProjects.ready().then(function(){
                    that.renderProjectsWidget();
                });
            });
        }
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
