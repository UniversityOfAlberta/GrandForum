LIMSTaskEditViewPmm = Backbone.View.extend({

    tagName: "tr",

    project: null,

    editDialog: null,


    initialize: function(options){
        this.project = options.project;
        this.model.saving = false;
        this.listenTo(this.model, "sync", this.render);
        this.listenTo(this.model, "change:assignees", this.assigneesChanged);
        this.selectTemplate();
        this.model.startTracking();

    },
    
    selectTemplate: function(){
        // Get project role for current user
        var userRole = _.pluck(_.filter(me.get('roles'), function(el){return el.title == this.project.get("name") ||  el.role !== PL}.bind(this)), 'role');
        // Memebers can only change 'assigned' -> 'done'
        var isPLAllowed = _.intersection(userRole, [PL, STAFF, MANAGER, ADMIN]).length > 0 ;

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
        "click #deleteTask": "deleteTask",
        "click #changeStatusButton": "changeStatus"
    },

    assigneesChanged: function () {
        var assIds   = _.pluck(this.model.get('assignees') || [], 'id');
        var $sel     = this.$('select[name=reviewer_id]');
        var members  = this.project.members;

        // if no assignees then disable reviewer
        if (assIds.length === 0) {
            this.model.unset('reviewer', {silent: true});
            $sel.prop('disabled', true).val('').empty()
                .trigger('chosen:updated');
            return;
        }

        // get the available reviewers
        var availIds = members.pluck('id').filter(function (id) {
            return !_.contains(assIds, id);
        });

        $sel.empty();
        _.each(availIds, function (id) {
            var p = members.get(id);
            $sel.append(
                $('<option/>').val(id).text(p.get('fullName'))
            );
        });

        // If everyone is an assignee then no reviewer.
        if (availIds.length === 0) { 
            this.model.unset('reviewer', {silent: true});
            $sel.prop('disabled', true).val('').trigger('chosen:updated');
            return;
        } else {
            $sel.prop('disabled', false);
        }

        // get existing reviewer if any
        var curObj = this.model.get('reviewer') || {};
        var curId  = curObj.id;

        // check if reviewer is still valid 
        var keep = curId && !_.contains(assIds, curId) && _.contains(availIds, curId);

        if (keep) {
            $sel.val(curId).trigger('chosen:updated');
            return;
        }

        // assign a new random reviewer
        var newId  = _.sample(availIds);
        var person = members.get(newId);

        this.model.set('reviewer', {
            id:   newId,
            name: person.get('fullName'),
            url:  person.get('url') || person.get('profileUrl')
        });

        $sel.val(newId).trigger('chosen:updated');
    },

    deleteTask: function(){
        this.model.toDelete = true;
        this.model.trigger("change:toDelete");
    },

    changeStatus: function(){
        // Create a model for the status change dialog
        var view = new LIMSStatusChangeViewPmm({el: this.editDialog, model: this.model, isDialog: true, project: this.project});
        
        this.editDialog.view = view;
        $('body').append(this.editDialog);

        // Check if the dialog is already initialized
        if (this.editDialog.dialog('instance')) {
            this.editDialog.dialog('destroy');
        }
        
        $('body').append(this.editDialog);
        
        this.editDialog.dialog({
            height: $(window).height() * 0.75,
            width: 600,
            title: "Change Task Status"
        });

        // Open the dialog
        this.editDialog.dialog('open');
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
        this.selectTemplate();

        if(!this.model.saving){
            this.$el.html(this.template(this.model.toJSON()));
            _.defer(function(){
                this.$('select[name=assignees]').show().chosen();
                this.$('select[name=reviewer_id]').show().chosen();
                this.assigneesChanged();
            }.bind(this));
        }
        this.editDialog = this.$('#changeStatusDialog');


        this.renderTinyMCE();

        // if(!this.model.get('isAllowedToEdit')){
        //     this.$el.prepend('<td></td>');
        // }


        return this.$el;
    }

});