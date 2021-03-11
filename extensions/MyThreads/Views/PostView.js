PostView = Backbone.View.extend({
    
    parent: null,
    row: null,
    template: _.template($('#post_template').html()),
    isDialog: false,
    oldMessage: "",
    isComment: false,
    tinyMCEMention: null,
    
    initialize: function(options){
        this.parent = options.parent;
        if(options.isDialog != undefined){
            this.isDialog = options.isDialog;
        }
        if(options.isComment != undefined){
            this.isComment = options.isComment;
        }
        if(options.tinyMCEMention != undefined){
            this.tinyMCEMention = options.tinyMCEMention;
        }
        if(this.model.isNew()){
            this.render();
        }
        this.listenTo(this.model, "sync", this.render);
    },

    events: {
        "click .edit-icon": "editPost",
        "click .delete-icon": "deletePost",
        "click #submitPost": "submitPost",
        "click #cancel": "cancel",
        "click #save": "save",
    },
    
    editPost: function(){
        this.oldMessage = this.model.get('message');
        this.editing = true;
        this.render();
    },
    
    deletePost: function(){
        this.model.destroy({success: function(model, response){
            this.$el.remove();
        }.bind(this)});
    },

    submitPost: function(){
        this.model.save();
        this.parent.addNewRow();
    },
    
    cancel: function(){
        this.editing = false;
        this.model.set('message', this.oldMessage);
        this.render();
    },
    
    save: function(){
        this.editing = false;
        this.model.save();
    },
    
    setupTinyMCE: function(){
        var model = this.model;
        if($('#tinyMCEUpload').length == 0){
            $('body').append("<iframe id='tinyMCEUpload' name='tinyMCEUpload' style='display:none'></iframe>" +
                             "<form id='tinyMCEUploadForm' action='" + wgServer + wgScriptPath + "/index.php?action=tinyMCEUpload' target='tinyMCEUpload' method='post' enctype='multipart/form-data' style='width:0px;height:0;overflow:hidden;position:absolute;left:-1000px;z-index:10000;'>" +
                                 "<input name='image' type='file' accept='image/*,.pdf'>" +
                             "</form>");
            $('#tinyMCEUploadForm input').change(function(){
                $('#tinyMCEUploadForm').ajaxSubmit({
                    success: function(d){
                        eval(d);
                    }
                });
                $('#tinyMCEUploadForm input').val('');
            });
        }
        _.defer(function(){
            this.$('textarea').tinymce({
                theme: 'modern',
                relative_urls : false,
                convert_urls: false,
                menubar: false,
                plugins: 'link image charmap lists table paste mention',
                toolbar: [
                    'undo redo | bold italic underline | link image charmap | table | bullist numlist outdent indent | subscript superscript | alignleft aligncenter alignright alignjustify'
                ],
                mentions: {
                    source: this.tinyMCEMention
                },
                file_browser_callback: function(field_name, url, type, win) {
                    if(type=='image') $('#tinyMCEUploadForm input').click();
                },
                paste_data_images: true,
                invalid_elements: 'h1, h2, h3, h4, h5, h6, h7, font',
                imagemanager_insert_template : '<img src="{$url}" width="{$custom.width}" height="{$custom.height}" />',
                setup: function(ed){
                    var update = function(){
                        model.set('message', ed.getContent());
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
        var classes = new Array();
        var isMine = {"isMine": false};
        if(this.model.get('author').id == me.id || _.intersection(_.pluck(me.get('roles'), 'role'), [STAFF,MANAGER,ADMIN]).length > 0){
             isMine.isMine = true;
        }
        var mod = _.extend(this.model.toJSON(), isMine);
        this.$el.html(this.template(mod));
        this.setupTinyMCE();
        return this.$el;
    }
});
