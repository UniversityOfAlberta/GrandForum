StoryEditView = Backbone.View.extend({

    isDialog: false,
    parent: null,

    initialize: function(options){
        this.parent = this;
        this.listenTo(this.model, "sync", this.render);
        this.listenTo(this.model, "change:description", this.render);
        this.listenTo(this.model, "change:title", function(){
            if(!this.isDialog){
                main.set('title', striphtml(this.model.get('title')));
            }
        });
        if(options.isDialog != undefined){
            this.isDialog = options.isDialog;
        }
        this.template = _.template($('#story_edit_template').html());
        if(!this.model.isNew() && !this.isDialog){
            this.model.fetch();
        }
        else{
            _.defer(this.render);
        }
        $(document).click($.proxy(function(e){
            var popup = $("div.popupBox:visible").not(":animated").first();
        }, this));
    },
    
    events: {
        "click #saveStory": "saveStory",
        "click #cancel": "cancel",
    },
    
    validate: function(){
        if(this.model.get('title').trim() == ""){
            return "The Story must have a title";
        }
        else if(this.model.get('story').trim() == ""){
            return "The Story must have a description";
        }
        return "";
    },
    
    saveStory: function(){
        var validation = this.validate();
        if(validation != ""){
            clearAllMessages();
            addError(validation, true);
            return;
        }
        this.$(".throbber").show();
        this.$("#saveStory").prop('disabled', true);
        this.model.save(null, {
            success: $.proxy(function(){
                this.$(".throbber").hide();
                this.$("#saveStory").prop('disabled', false);
                clearAllMessages();
                document.location = this.model.get('story_url');
            }, this),
            error: $.proxy(function(m, e){
                this.$(".throbber").hide();
                this.$("#saveStory").prop('disabled', false);
                clearAllMessages();
                addError(e.responseText, true);
            }, this)
        });
    },
    
    cancel: function(){
        document.location = this.model.get('url');
    },
    
    renderAuthors: function(){
        if(this.allPeople != null && this.allPeople.length > 0){
        }
        else{
            this.allPeople = new People();
            this.allPeople.fetch();
            this.allPeople.bind('sync', function(){
                if(this.allPeople.length > 0){
                }
            }, this);
        }
    },
    
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        this.renderAuthors();
        return this.$el;
    }

});
