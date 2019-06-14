JobPostingEditView = Backbone.View.extend({

    allProjects: null,

    initialize: function(){
        this.model.fetch({
            error: function(e){
                this.$el.html("This Job Posting does not exist");
            }.bind(this)
        });
        this.listenTo(this.model, "sync", function(){
            this.allProjects = new Projects();
            this.allProjects.fetch();
            this.listenTo(this.allProjects, "sync", function(){
                me.getProjects();
                me.projects.ready().then(function(){
                    if(this.model.isNew() && me.projects.length > 0){
                        this.model.set('projectId', me.projects.first().get('projectId'));
                    }
                    this.render();
                    this.checkProjectContact();
                }.bind(this));
            }.bind(this));
        }.bind(this));
        this.listenTo(this.model, "change:projectId", this.checkProjectContact);
        this.listenTo(this.model, "change:rank", this.updateRank);
        this.listenTo(this.model, "change:title", function(){
            main.set('title', this.model.get('title'));
        });
        this.template = _.template($('#jobposting_edit_template').html());
    },
    
    saveJobPosting: function(){
        if (this.model.get("jobTitle").trim() == '') {
            clearWarning();
            addWarning('Title must not be empty', true);
            return;
        }
        this.$(".throbber").show();
        this.$("#saveJobPosting").prop('disabled', true);
        this.model.save(null, {
            success: function(){
                this.$(".throbber").hide();
                this.$("#saveJobPosting").prop('disabled', false);
                clearAllMessages();
                document.location = this.model.get('url');
            }.bind(this),
            error: function(o, e){
                this.$(".throbber").hide();
                this.$("#saveJobPosting").prop('disabled', false);
                clearAllMessages();
                if(e.responseText != ""){
                    addError(e.responseText, true);
                }
                else{
                    addError("There was a problem saving the Job Posting", true);
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
        
        "click #saveJobPosting": "saveJobPosting",
        "click #cancel": "cancel"
    },
    
    characterCount: function(){
        _.defer(function(){
            this.$("#characterCount").text(this.$("textarea[name=summary]").val().length);
            this.$("#characterCountFr").text(this.$("textarea[name=summaryFr]").val().length);
        }.bind(this));
    },
    
    checkProjectContact: function(){
        this.$("#contactError").hide();
        if(this.allProjects != null && this.model.get('projectId') != 0){
            var contact = this.allProjects.get(this.model.get('projectId')).get('contact');
            if(_.isEmpty(contact.city) ||
               _.isEmpty(contact.province) ||
               _.isEmpty(contact.country) ||
               _.isEmpty(contact.code) ||
               _.isEmpty(contact.phone) ||
               _.isEmpty(contact.line1) ||
               _.isEmpty(contact.email)){
                this.$("#contactError").html("The department contact information is incomplete.  <a href='" + this.allProjects.get(this.model.get('projectId')).get('url') + "' target='_blank'>Click here</a> to update it.");
                this.$("#contactError").show();
            }
        }
    },
    
    updateRank: function(){
        if(this.model.get('rank') == "Other"){
            this.$("input[name=rankOther]").show();
        }
        else{
            this.$("input[name=rankOther]").hide();
        }
    },
    
    renderResearchFieldsWidget: function(){
        var objs = {};
        var delimiter = ',';
        var html = HTML.TagIt(this, 'researchFields', {
            values: this.model.get('researchFields').split(','),
            strictValues: false, 
            objs: objs,
            options: {
                placeholderText: 'Enter fields here...',
                allowSpaces: true,
                allowDuplicates: false,
                removeConfirmation: false,
                singleFieldDelimiter: delimiter,
                splitOn: delimiter
            }
        });
        this.$("#researchFields").html(html);
        this.$("#researchFields .tagit").css("margin-top", 0)
                                        .css("margin-bottom", 0);
                                        
        var objs = {};
        var delimiter = ',';
        var html = HTML.TagIt(this, 'researchFieldsFr', {
            values: this.model.get('researchFieldsFr').split(','),
            strictValues: false, 
            objs: objs,
            options: {
                placeholderText: 'Enter fields here...',
                allowSpaces: true,
                allowDuplicates: false,
                removeConfirmation: false,
                singleFieldDelimiter: delimiter,
                splitOn: delimiter
            }
        });
        this.$("#researchFieldsFr").html(html);
        this.$("#researchFieldsFr .tagit").css("margin-top", 0)
                                          .css("margin-bottom", 0);
    },
    
    renderKeywordsWidget: function(){
        var objs = {};
        var delimiter = ',';
        var html = HTML.TagIt(this, 'keywords', {
            values: this.model.get('keywords').split(','),
            strictValues: false, 
            objs: objs,
            options: {
                placeholderText: 'Enter keywords here...',
                allowSpaces: true,
                allowDuplicates: false,
                removeConfirmation: false,
                singleFieldDelimiter: delimiter,
                splitOn: delimiter
            }
        });
        this.$("#keywords").html(html);
        this.$("#keywords .tagit").css("margin-top", 0)
                                  .css("margin-bottom", 0);
                                  
        var objs = {};
        var delimiter = ',';
        var html = HTML.TagIt(this, 'keywordsFr', {
            values: this.model.get('keywordsFr').split(','),
            strictValues: false, 
            objs: objs,
            options: {
                placeholderText: 'Enter keywords here...',
                allowSpaces: true,
                allowDuplicates: false,
                removeConfirmation: false,
                singleFieldDelimiter: delimiter,
                splitOn: delimiter
            }
        });
        this.$("#keywordsFr").html(html);
        this.$("#keywordsFr .tagit").css("margin-top", 0)
                                    .css("margin-bottom", 0);
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
            main.set('title', 'New Job Posting');
        }
        else {
            main.set('title', 'Edit Job Posting');
        }
        this.$el.html(this.template(this.model.toJSON()));
        this.renderKeywordsWidget();
        this.renderResearchFieldsWidget();
        //this.renderTinyMCE();
        this.characterCount();
        this.$('[name=projectId]').chosen();
        return this.$el;
    }

});
