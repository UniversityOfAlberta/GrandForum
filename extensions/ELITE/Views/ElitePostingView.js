ElitePostingView = PostingView.extend({

    template: _.template($('#eliteposting_template').html()),
    
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
            this.changeType();
            this.render();
        }.bind(this));
    },
    
    changeType: function(){
        if(this.model.get('type') == "Intern"){
            this.template = _.template($('#eliteposting_template').html());
        }
        else if(this.model.get('type') == "PhD"){
            this.template = _.template($('#eliteposting_phd_template').html());
        }
    },

    render: function(){
        if(!this.isDialog){
            main.set('title', showLanguage(this.model.get('language'), this.model.get('title'), this.model.get('titleFr')));
        }
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
