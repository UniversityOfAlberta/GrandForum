NewsPostingView = Backbone.View.extend({

    translated: new NewsPosting(),

    initialize: function(){
        this.model.fetch({
            error: function(e){
                this.$el.html("This News Posting does not exist");
            }.bind(this)
        });
        this.listenTo(this.model, "sync", function(){
            this.translated.set('id', this.model.get('translatedId'));
            this.translated.fetch();
            this.render();
        }.bind(this));
        this.listenTo(this.translated, "sync", this.render);
        this.template = _.template($('#newsposting_template').html());
    },
    
    events: {
        "click #editNewsPosting": "editNewsPosting",
        "click #deleteNewsPosting": "deleteNewsPosting"
    },
    
    deleteNewsPosting: function(){
        if(this.model.get('deleted') != true){
            if(confirm("Are you sure you want to delete this News Posting?")){
                this.model.destroy({
                    success: function(model, response) {
                        if(response.deleted == true){
                            model.set(response);
                            clearSuccess();
                            clearError();
                            addSuccess('The News Posting <i>' + response.title + '</i> was deleted sucessfully');
                        }
                        else{
                            clearSuccess();
                            clearError();
                            addError('The News Posting <i>' + response.title + '</i> was not deleted sucessfully');
                        }
                    },
                    error: function(model, response) {
                        clearSuccess();
                        clearError();
                        addError('The News Posting <i>' + response.title + '</i> was not deleted sucessfully');
                    }
                });
            }
        }
        else{
            clearAllMessages();
            addError('This ' + this.model.get('category') + ' is already deleted');
        }
    },
    
    editNewsPosting: function(){
        document.location = document.location + '/edit';
    },
    
    render: function(){
        main.set('title', this.model.get('title'));
        this.$el.empty();
        var data = this.model.toJSON();
        _.extend(data, dateTimeHelpers);
        this.$el.html(this.template(data));
        if(this.model.get('deleted') == true){
            this.$el.find("#deleteNewsPosting").prop('disabled', true);
            clearInfo();
            addInfo('This News Posting has been deleted, and will not show up anywhere else on the forum.  You may still edit the News Posting.');
        }
        return this.$el;
    }

});
