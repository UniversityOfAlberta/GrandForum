BSIPostingEditView = PostingEditView.extend({

    template: _.template($('#bsiposting_edit_template').html()),
    
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
                plugins: 'link image charmap lists table paste',
                toolbar: [
                    'undo redo | bold italic underline | link charmap | bullist numlist outdent indent | subscript superscript | alignleft aligncenter alignright alignjustify'
                ],
                paste_data_images: true,
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
    
    renderTagsWidget: function(){
        var html = HTML.TagIt(this, 'discipline', {
            strictValues: false, 
            values: this.model.get('discipline').split('; '),
            options: {
                removeConfirmation: false,
                availableTags: allDepartments,
                singleFieldDelimiter: ";"
            }
        });
        this.$("#discipline").html(html);
    },
    
    postRender: function(){
        this.renderTagsWidget();
        this.renderTinyMCE('about');
        this.renderTinyMCE('summary');
        this.renderTinyMCE('skills');
    }

});
