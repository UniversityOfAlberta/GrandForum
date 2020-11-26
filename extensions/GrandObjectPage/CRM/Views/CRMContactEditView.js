CRMContactEditView = Backbone.View.extend({

    initialize: function(){
        this.model.fetch();
        this.listenTo(this.model, "sync", this.render);
        this.listenTo(this.model.opportunities, "sync", this.renderOpportunities);
        this.listenTo(this.model.opportunities, "add", this.renderOpportunities);
        this.listenTo(this.model.opportunities, "remove", this.renderOpportunities);
        this.listenTo(this.model, "change:title", function(){
            main.set('title', this.model.get('title'));
        });
        this.template = _.template($('#crm_contact_edit_template').html());
    },
    
    addOpportunity: function(){
        this.model.opportunities.add(new CRMOpportunity({contact: this.model.get('id')}));
    },
    
    save: function(){
        this.$(".throbber").show();
        this.$("#save").prop('disabled', true);
        this.model.save(null, {
            success: function(){
                this.saveOpportunities();
            }.bind(this),
            error: function(o, e){
                this.$(".throbber").hide();
                this.$("#save").prop('disabled', false);
                clearAllMessages();
                if(e.responseText != ""){
                    addError(e.responseText, true);
                }
                else{
                    addError("There was a problem saving the Contact", true);
                }
            }.bind(this)
        });
    },
    
    saveOpportunities: function(){
        var xhrs = [];
        this.model.opportunities.each(function(model){
            xhrs.push(model.save());
        });
        $.when.apply(null, xhrs).done(function(){
            this.saveTasks();
        }.bind(this));
    },
    
    saveTasks: function(){
        var xhrs = [];
        this.model.opportunities.each(function(model){
            model.tasks.each(function(task){
                xhrs.push(task.save());
            });
        });
        $.when.apply(null, xhrs).done(function(){
            this.$(".throbber").hide();
            this.$("#save").prop('disabled', false);
            clearAllMessages();
            document.location = this.model.get('url');
        }.bind(this));
    },
       
    events: {
        "click #addOpportunity": "addOpportunity",
        "click #save": "save",
        "click #cancel": "cancel"
    },
    
    cancel: function(){
        document.location = this.model.get('url');
    },
        
    renderOpportunities: function(){
        this.$("#opportunities").empty();
        this.model.opportunities.each(function(model){
            var view = new CRMOpportunityEditView({model: model});
            this.$("#opportunities").append(view.render());
            this.$("#opportunities").append("<hr />");
        }.bind(this));
        this.$("#opportunities hr").last().remove();
    },
    
    render: function(){
        main.set('title', this.model.get('title'));
        this.$el.html(this.template(this.model.toJSON()));
        this.renderOpportunities();
        return this.$el;
    }

});
