PostingEditView = Backbone.View.extend({

    initialize: function(){
        this.model.fetch({
            error: function(e){
                this.$el.html("This Posting does not exist");
            }.bind(this)
        });
        this.listenTo(this.model, "sync", function(){
            this.render();
        }.bind(this));
        this.listenTo(this.model, "change:language", function(){
            main.set('title', showLanguage(this.model.get('language'), this.model.get('title'), this.model.get('titleFr')));
        });
        this.listenTo(this.model, "change:title", function(){
            main.set('title', showLanguage(this.model.get('language'), this.model.get('title'), this.model.get('titleFr')));
        });
        this.listenTo(this.model, "change:titleFr", function(){
            main.set('title', showLanguage(this.model.get('language'), this.model.get('title'), this.model.get('titleFr')));
        });
    },
    
    savePosting: function(){
        this.$(".throbber").show();
        this.$("#savePosting").prop('disabled', true);
        this.model.save(null, {
            success: function(){
                this.$(".throbber").hide();
                this.$("#savePosting").prop('disabled', false);
                clearAllMessages();
                document.location = this.model.get('url');
            }.bind(this),
            error: function(o, e){
                this.$(".throbber").hide();
                this.$("#savePosting").prop('disabled', false);
                clearAllMessages();
                if(e.responseText != ""){
                    addError(e.responseText, true);
                }
                else{
                    addError("There was a problem saving the Posting", true);
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
        
        "keyup textarea[name=summaryFr]": "characterCount",
        "cut textarea[name=summaryFr]": "characterCount",
        "paste textarea[name=summaryFr]": "characterCount",
        
        "click #savePosting": "savePosting",
        "click #cancel": "cancel"
    },
    
    characterCount: function(){
        _.defer(function(){
            this.$("#characterCount").text(this.$("textarea[name=summary]").val().length);
            //this.$("#characterCountFr").text(this.$("textarea[name=summaryFr]").val().length);
        }.bind(this));
    },
    
    renderTinyMCE: function(){
        var model = this.model;
        _.defer(function(){
            this.$('textarea').tinymce({
                theme: 'modern',
                menubar: false,
                relative_urls : false,
                convert_urls: false,
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
    
    postRender: function(){
    
    },
    
    render: function(){
        if(this.model.isNew()){
            main.set('title', 'New Posting');
        }
        else {
            main.set('title', showLanguage(this.model.get('language'), this.model.get('title'), this.model.get('titleFr')));
        }
        this.$el.html(this.template(this.model.toJSON()));
        //this.renderTinyMCE();
        this.characterCount();
        this.postRender();
        return this.$el;
    }

});
