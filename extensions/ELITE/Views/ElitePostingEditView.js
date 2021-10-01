ElitePostingEditView = PostingEditView.extend({

    template: _.template($('#eliteposting_edit_template').html()),
    
    initialize: function(){
         this.model.fetch({
            error: function(e){
                this.$el.html("This Posting does not exist");
            }.bind(this)
        });
        this.listenTo(this.model, "sync", function(){
            this.render();
        }.bind(this));
        this.listenTo(this.model, "change:type", this.changeType);
    },
    
    changeType: function(){
        if(this.model.get('type') == "Intern"){
            this.template = _.template($('#eliteposting_edit_template').html());
        }
        else if(this.model.get('type') == "PhD"){
            this.template = _.template($('#eliteposting_phd_edit_template').html());
        }
        this.render();
    },
    
    characterCount: function(){
        return true;
    },
    
    renderTinyMCE: function(name){
        var model = this.model;
        _.defer(function(){
            this.$('textarea[name=' + name + ']').tinymce({
                theme: 'modern',
                menubar: false,
                relative_urls : false,
                convert_urls: false,
                plugins: 'link charmap lists table paste',
                toolbar: [
                    'undo redo | bold italic underline | link charmap | bullist numlist outdent indent | subscript superscript | alignleft aligncenter alignright alignjustify'
                ],
                paste_data_images: false,
                invalid_elements: 'h1, h2, h3, h4, h5, h6, h7, font',
                imagemanager_insert_template : '<img src="{$url}" width="{$custom.width}" height="{$custom.height}" />',
                setup: function(ed){
                    var update = function(){
                        model.set(name, ed.getContent());
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
        if(this.model.isNew()){
            main.set('title', 'New Project Proposal');
        }
        if(this.model.get('visibility') == 'Requested More Info'){
            this.model.set('visibility', 'Submitted More Info');
        }
        this.$("[name=basedAt]").combobox();
        this.$("[name=positions]").forceNumeric({min: 0, max: 1000,includeCommas: false});
        this.renderTinyMCE('extra_companyProfile');
        this.renderTinyMCE('summary');
        this.renderTinyMCE('extra_training');
        this.renderTinyMCE('extra_responsibilities');
        this.renderTinyMCE('extra_qualifications');
        this.renderTinyMCE('extra_skills');
        this.$("input[name=extra_ack1]").change(function(){
            var value = this.$("input[name=extra_ack1]:checked").val();
            if(value == "No"){
                this.$("#acknowledgment1Warning").slideDown();
            }
            else{
                this.$("#acknowledgment1Warning").slideUp();
            }
        }.bind(this)).change();
        this.$("input[name=extra_ack2]").change(function(){
            var value = this.$("input[name=extra_ack2]:checked").val();
            if(value == "No"){
                this.$("#acknowledgment2Warning").slideDown();
            }
            else{
                this.$("#acknowledgment2Warning").slideUp();
            }
        }.bind(this)).change();
    }

});
