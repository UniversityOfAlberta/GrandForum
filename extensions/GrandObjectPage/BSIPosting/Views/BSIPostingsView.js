BSIPostingsView = PostingsView.extend({

    template: _.template($('#bsipostings_template').html()),
    
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
    }

});
