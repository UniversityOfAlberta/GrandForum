LIMSTaskEditViewPmm = Backbone.View.extend({

    tagName: "tr",

    project: null,

    initialize: function(options){
        this.project = options.project;
        // console.log(this.project);
        this.model.saving = false;
        this.listenTo(this.model, "sync", this.render);
        this.selectTemplate();

    },
    
    selectTemplate: function(){
        // Get project role for current user
        var userRole = _.pluck(_.filter(me.get('roles'), function(el){return el.title == this.project.get("name") ||  el.role !== PL}.bind(this)), 'role');
        // Memebers can only change 'assigned' -> 'done'
        var isPLAllowed = _.intersection(userRole, [PL, STAFF, MANAGER, ADMIN]).length > 0 ;

        console.log(userRole,isPLAllowed);
        
        var isMemberAllowed = !isPLAllowed && (this.model.get('status') == 'Assigned' || this.model.get('status') == 'Done');
        

        this.model.set('isLeaderAllowedToEdit', isPLAllowed);
        this.model.set('isMemberAllowedToEdit', isMemberAllowed);


        if(!this.model.get('isAllowedToEdit')){
            // Not allowed to edit, use read-only version
            this.template = _.template($('#lims_task_template').html());
        }
        else{
            // Use Edit version
            this.template = _.template($('#lims_task_edit_template').html());
        }
    },
    
    events: {
        "click #deleteTask": "deleteTask"
    },
    
    deleteTask: function(){
        this.model.toDelete = true;
        this.model.trigger("change:toDelete");
    },

    renderTinyMCE: function(){
        _.defer(function(){
            this.$('textarea').tinymce({
                theme: 'modern',
                menubar: false,
                statusbar: false,
                relative_urls : false,
                convert_urls: false,
                plugins: 'link image charmap lists table paste',
                toolbar: [
                    'bold | link | bullist numlist'
                ],
                paste_data_images: true,
                invalid_elements: 'h1, h2, h3, h4, h5, h6, h7, font',
                imagemanager_insert_template : '<img src="{$url}" width="{$custom.width}" height="{$custom.height}" />',
                setup: function(editor){
                    editor.on('change', function(e){
                        this.model.set('comments',editor.getContent());
                    }.bind(this));
                }.bind(this)

            });
        }.bind(this));
    },

    render: function(){
        // for (edId in tinyMCE.editors){
        //     var e = tinyMCE.editors[edId];
        //     if(e != undefined){
        //         e.destroy();
        //         e.remove();
        //     }
        // }

        if(!this.model.saving){
            this.$el.html(this.template(this.model.toJSON()));
            _.defer(function(){
                this.$('select[name=assignee_id]').chosen();
            }.bind(this));
        }

        this.renderTinyMCE();
        return this.$el;
    }

});
