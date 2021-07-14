PostingView = Backbone.View.extend({

    isDialog: false,

    initialize: function(options){
        if(options.isDialog != undefined){
            this.isDialog = options.isDialog;
        }
        this.model.fetch({
            error: function(e){
                this.$el.html("This Posting does not exist");
            }.bind(this)
        });
        this.listenTo(this.model, "sync", function(){
            this.render();
        }.bind(this));
    },
    
    events: {
        "click #editPosting": "editPosting",
        "click #deletePosting": "deletePosting",
        "click #previewLink": "clickPreviewLink"
    },
    
    deletePosting: function(){
        if(this.model.get('deleted') != true){
            if(confirm("Are you sure you want to delete this Posting?")){
                this.model.destroy({
                    success: function(model, response) {
                        if(response.deleted == true){
                            model.set(response);
                            clearSuccess();
                            clearError();
                            addSuccess('The Posting <i>' + response.title + '</i> was deleted sucessfully');
                        }
                        else{
                            clearSuccess();
                            clearError();
                            addError('The Posting <i>' + response.title + '</i> was not deleted sucessfully');
                        }
                    },
                    error: function(model, response) {
                        clearSuccess();
                        clearError();
                        addError('The Posting <i>' + response.title + '</i> was not deleted sucessfully');
                    }
                });
            }
        }
        else{
            clearAllMessages();
            addError('This ' + this.model.get('category') + ' is already deleted');
        }
    },
    
    editPosting: function(){
        document.location = document.location + '/edit';
    },
    
    clickPreviewLink: function(){
        _.delay(function(){
            this.model.fetch();
        }.bind(this), 3000);
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
        if(this.model.get('visibility') == 'Draft'){
            clearWarning();
            addWarning('This posting is in Draft form. It must be Published before it will be visible to the public. To do this, click the Edit Posting button below, change Visiblity to Published on the posting form, and then click the Save Posting button.');
        }
        return this.$el;
    }

});
