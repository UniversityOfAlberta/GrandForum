ThreadEditView = Backbone.View.extend({

    isDialog: false,
    parent: null,
    allPeople: null,

    initialize: function(options){
        this.parent = this;
        this.listenTo(this.model, "sync", this.render);
        this.listenTo(this.model, "change:title", function(){
            if(!this.isDialog){
                main.set('title', striphtml(this.model.get('title')));
            }
        });
        if(options.isDialog != undefined){
            this.isDialog = options.isDialog;
        }
        this.template = _.template($('#thread_edit_template').html());
        if(!this.model.isNew() && !this.isDialog){
            this.model.fetch();
        }
        else{
            _.defer(this.render);
        }
    },
    
    events: {
        "click #saveThread": "saveThread",
        "click #cancel": "cancel",
    },
    
    validate: function(){
        if(this.model.get('title').trim() == ""){
            return "The Thread must have a title";
        }
        return "";
    },
    
    saveThread: function(){
        var validation = this.validate();
        if(validation != ""){
            clearAllMessages();
            addError(validation, true);
            return;
        }
        this.$(".throbber").show();
        this.$("#saveThread").prop('disabled', true);
        this.model.save(null, {
            success: function(){
                this.$(".throbber").hide();
                this.$("#saveThread").prop('disabled', false);
                clearAllMessages();
                document.location = this.model.get('url');
            }.bind(this),
            error: function(){
                this.$(".throbber").hide();
                this.$("#saveThread").prop('disabled', false);
                clearAllMessages();
                addError("There was a problem saving the Thread", true);
            }.bind(this)
        });
    },
    
    cancel: function(){
        document.location = this.model.get('url');
    },
    
    renderAuthorsWidget: function(){
        var left = _.pluck(this.model.get('authors'), 'name');
        var right = _.difference(this.allPeople.pluck('name'), left);
        var html = HTML.Switcheroo(this, 'authors.name', {name: 'author',
                                                          'left': left,
                                                          'right': right
                                                          });
        this.$("#threadPeople").html(html);
        createSwitcheroos();
    },
    
    renderAuthors: function(){
        if(this.allPeople != null && this.allPeople.length > 0){
            this.renderAuthorsWidget();
        }
        else{
            this.allPeople = new People();
            this.allPeople.fetch();
            this.allPeople.bind('sync', function(){
                if(this.allPeople.length > 0){
                    this.renderAuthorsWidget();
                }
            }, this);
        }
    },
    
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        //this.renderAuthors();
        return this.$el;
    }

});
