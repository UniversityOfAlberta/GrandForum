ElitePostingView = PostingView.extend({

    template: _.template($('#eliteposting_template').html()),
    
    deletePosting: function(e) {
        var deleteDialog = $("#deleteDialog").dialog({
            autoOpen: false,
	        modal: true,
            buttons: {
	            "Delete": function(){
	                var model = this.model;
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
        main.set('title', showLanguage(this.model.get('language'), this.model.get('title'), this.model.get('titleFr')));
        this.$el.empty();
        var data = this.model.toJSON();
        _.extend(data, dateTimeHelpers);
        this.$el.html(this.template(data));
        if(this.model.get('deleted') == true){
            this.$el.find("#deletePosting").prop('disabled', true);
            clearInfo();
            addInfo('This Posting has been deleted, and will not show up anywhere else on the forum.  You may still edit the Posting.');
        }
        return this.$el;
    }

});
