JobPostingView = Backbone.View.extend({

    allProjects: null,

    initialize: function(){
        this.model.fetch({
            error: function(e){
                this.$el.html("This Job Posting does not exist");
            }.bind(this)
        });
        this.listenTo(this.model, "sync", function(){
            this.allProjects = new Projects();
            this.allProjects.fetch();
            this.listenTo(this.allProjects, "sync", this.render);
        }.bind(this));
        this.template = _.template($('#jobposting_template').html());
    },
    
    events: {
        "click #editJobPosting": "editJobPosting",
        "click #deleteJobPosting": "deleteJobPosting",
        "click #previewLink": "clickPreviewLink"
    },
    
    deleteJobPosting: function(){
        if(this.model.get('deleted') != true){
            if(confirm("Are you sure you want to delete this Job Posting?")){
                this.model.destroy({
                    success: function(model, response) {
                        if(response.deleted == true){
                            model.set(response);
                            clearSuccess();
                            clearError();
                            addSuccess('The Job Posting <i>' + response.title + '</i> was deleted sucessfully');
                        }
                        else{
                            clearSuccess();
                            clearError();
                            addError('The Job Posting <i>' + response.title + '</i> was not deleted sucessfully');
                        }
                    },
                    error: function(model, response) {
                        clearSuccess();
                        clearError();
                        addError('The Job Posting <i>' + response.title + '</i> was not deleted sucessfully');
                    }
                });
            }
        }
        else{
            clearAllMessages();
            addError('This ' + this.model.get('category') + ' is already deleted');
        }
    },
    
    editJobPosting: function(){
        document.location = document.location + '/edit';
    },
    
    clickPreviewLink: function(){
        _.delay(function(){
            this.model.fetch();
        }.bind(this), 3000);
    },
    
    render: function(){
        main.set('title', this.model.get('jobTitle'));
        this.$el.empty();
        var data = this.model.toJSON();
        _.extend(data, dateTimeHelpers);
        this.$el.html(this.template(data));
        if(this.model.get('deleted') == true){
            this.$el.find("#deleteJobPosting").prop('disabled', true);
            clearInfo();
            addInfo('This Job Posting has been deleted, and will not show up anywhere else on the forum.  You may still edit the Job Posting.');
        }
        if(this.model.get('visibility') == 'Draft'){
            clearWarning();
            addWarning('This job ad is in Draft form. It must be Published before it will be visible to the public. To do this, click the Edit Job Posting button below, change Visiblity to Published on the posting form, and then click the Save Job Posting button.');
        }
        return this.$el;
    }

});
