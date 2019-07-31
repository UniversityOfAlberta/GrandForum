NewsPostingEditView = Backbone.View.extend({

    initialize: function(){
        this.model.fetch({
            error: function(e){
                this.$el.html("This News Posting does not exist");
            }.bind(this)
        });
        this.listenTo(this.model, "sync", function(){
            this.render();
        }.bind(this));
        this.listenTo(this.model, "change:title", function(){
            main.set('title', this.model.get('title'));
        });
        this.template = _.template($('#newsposting_edit_template').html());
    },
    
    saveNewsPosting: function(){
        if (this.model.get("title").trim() == '') {
            clearWarning();
            addWarning('Title must not be empty', true);
            return;
        }
        this.$(".throbber").show();
        this.$("#saveNewsPosting").prop('disabled', true);
        var bilingual = this.model.get('bilingual');
        this.model.save(null, {
            success: function(){
                this.$(".throbber").hide();
                this.$("#saveNewsPosting").prop('disabled', false);
                clearAllMessages();
                if(bilingual == "Yes"){
                    var oldModel = this.model;
                    this.model = new NewsPosting({translatedId: oldModel.get('id')});
                    this.model.toggleLanguage();
                    this.render();
                    addSuccess("News Posting created.  Bilingual was set to 'Yes' so enter the information for the '" + this.model.get('language') + "' language.", true);
                }
                else{
                    document.location = this.model.get('url');
                }
            }.bind(this),
            error: function(o, e){
                this.$(".throbber").hide();
                this.$("#saveNewsPosting").prop('disabled', false);
                clearAllMessages();
                if(e.responseText != ""){
                    addError(e.responseText, true);
                }
                else{
                    addError("There was a problem saving the News Posting", true);
                }
            }.bind(this)
        });
    },
    
    cancel: function(){
        document.location = this.model.get('url');
    },
    
    events: {
        "keyup textarea[name=summary]": "characterCount",
        "cut textarea[name=summary]": "characterCount",
        "paste textarea[name=summary]": "characterCount",
        
        "click #saveNewsPosting": "saveNewsPosting",
        "click #cancel": "cancel"
    },
    
    characterCount: function(){
        _.defer(function(){
            this.$("#characterCount").text(this.$("textarea[name=summary]").val().length);
        }.bind(this));
    },
    
    renderTinyMCE: function(){
        var model = this.model;
        _.defer(function(){
            this.$('textarea').tinymce({
                theme: 'modern',
                menubar: false,
                plugins: 'link image charmap lists table paste',
                toolbar: [
                    'undo redo | bold italic underline | link charmap | table | bullist numlist outdent indent | subscript superscript | alignleft aligncenter alignright alignjustify'
                ],
                paste_data_images: true,
                invalid_elements: 'h1, h2, h3, h4, h5, h6, h7, font',
                imagemanager_insert_template : '<img src="{$url}" width="{$custom.width}" height="{$custom.height}" />',
                setup: function(ed){
                    var update = function(){
                        model.set('summary', ed.getContent());
                    };
                    ed.on('keydown', update);
                    ed.on('keyup', update);
                    ed.on('change', update);
                    ed.on('init', update);
                    ed.on('blur', update);
                }
            });
        }.bind(this));
    },
    
    render: function(){
        if(this.model.isNew()){
            main.set('title', 'New News Posting');
        }
        else {
            main.set('title', 'Edit News Posting');
        }
        this.$el.html(this.template(this.model.toJSON()));
        //this.renderTinyMCE();
        this.characterCount();
        return this.$el;
    }

});
