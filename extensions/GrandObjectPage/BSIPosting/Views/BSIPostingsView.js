BSIPostingsView = PostingsView.extend({

    template: _.template($('#bsipostings_template').html()),
    
    initialize: function(){
        this.deleted = new BSIPostings();
        this.deleted.deleted = true;
        this.model.fetch();
        main.set('title', 'Postings');
        this.listenTo(this.model, "sync", function(){
            this.deleted.fetch();
        }.bind(this));
        this.listenTo(this.model, "remove", function(){
            this.deleted.fetch();
        }.bind(this));
        this.listenTo(this.deleted, "sync", this.render);
    },
    
    delete: function(e) {
        var deleteDialog = this.$("#deleteDialog" + e.target.id).dialog({
            autoOpen: false,
	        modal: true,
            buttons: {
	            "Delete": function(){
	                var model = this.model.get(e.target.id);
	                if(model.get('deleted') != true){
	                    $("div.throbber", deleteDialog).show();
	                    model.save(null, {
	                        success: function(){
                                model.destroy({
                                    success: function(model, response) {
                                        deleteDialog.dialog('close');
                                        $("div.throbber", deleteDialog).hide();
                                        if(response.deleted == true){
                                            model.set(response);
                                            clearSuccess();
                                            clearError();
                                            addSuccess('The Posting was deleted sucessfully');
                                        }
                                        else{
                                            clearSuccess();
                                            clearError();
                                            addError('The Posting was not deleted sucessfully');
                                        }
                                    }.bind(this),
                                    error: function(model, response) {
                                        deleteDialog.dialog('close');
                                        clearSuccess();
                                        clearError();
                                        addError('The Posting was not deleted sucessfully');
                                    }.bind(this)
                                });
                            }.bind(this),
                            error: function(){
                                deleteDialog.dialog('close');
                                clearSuccess();
                                clearError();
                                addError('The Posting was not deleted sucessfully');
                            }.bind(this)
                        });
                    }
                    else{
                        deleteDialog.dialog('close');
                        clearAllMessages();
                        addError('This Posting is already deleted');
                    }
	            }.bind(this),
	            "Cancel": function(){
	                deleteDialog.dialog('close');
	            }.bind(this)
	        }
        });
        deleteDialog.parent().appendTo(this.$el);
        deleteDialog.dialog('open');
    },
    
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        this.$("table#postings").DataTable({
            "autoWidth": true,
            "order": [[ 0, "desc" ]]
        });
        this.$("table#deleted").DataTable({
            "autoWidth": true,
            "order": [[ 0, "desc" ]]
        });
        this.$("#showDeleted").click(function(){
            this.$("#showDeleted").hide();
            this.$("#deletedPostings").slideDown();
        }.bind(this));
        return this.$el;
    }

});
