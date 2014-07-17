ProductEditView = Backbone.View.extend({

    initialize: function(){
        this.listenTo(this.model, "sync", this.render);
        this.listenTo(this.model, "change:type", this.render);
        this.listenTo(this.model, "change:title", function(){
            main.set('title', this.model.get('title'));
        });
        this.template = _.template($('#product_edit_template').html());
        if(!this.model.isNew()){
            this.model.fetch();
        }
        else{
            _.defer(this.render);
        }
    },
    
    events: {
        "click #saveProduct": "saveProduct",
        "click #cancel": "cancel"
    },
    
    saveProduct: function(){
        if(this.model.get('title').trim() == ""){
            clearAllMessages();
            addError("The Product must have a title", true);
            return;
        }
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
                addError("There was a problem saving the Product", true);
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
        if(this.allPeople != null && this.allPeople.length > 0){
            this.renderAuthorsWidget();
        }
        else{
            this.allPeople = new People();
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
        if(this.allProjects != null && this.current != null){
            this.renderProjectsWidget();
        }
        else{
            this.allProjects = new Projects();
            var myProjects;
            var spin = spinner("productSpinner", 10, 20, 10, 3, '#888');
            $.when(this.allProjects.fetch(), 
                   this.myProjects = me.getProjects()).then($.proxy(function(){
                this.current = this.myProjects.getCurrent();
                this.myProjects.ready().then($.proxy(function(){
                    this.renderProjectsWidget();
                }, this));
            }, this));
        }
    },
    
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        this.renderAuthors();
        this.renderProjects();
        return this.$el;
    }

});
