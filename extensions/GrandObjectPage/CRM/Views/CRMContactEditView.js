CRMContactEditView = Backbone.View.extend({

    isDialog: false,
    subViews: [],
    saving: false,

    initialize: function(options){
        this.model.saving = false;
        if(!this.model.isNew()){
            this.model.fetch();
        }
        this.listenTo(this.model, "sync", this.render);
        //this.listenTo(this.model.opportunities, "sync", this.renderOpportunities);
        this.listenTo(this.model.opportunities, "add", this.renderOpportunities);
        this.listenTo(this.model.opportunities, "change:toDelete", this.removeOpportunities);
        this.listenTo(this.model, "change:title", function(){
            if(!this.isDialog){
                main.set('title', this.model.get('title'));
            }
        });
        if(options.isDialog != undefined){
            this.isDialog = options.isDialog;
        }
        this.template = _.template($('#crm_contact_edit_template').html());
    },
    
    addOpportunity: function(){
        this.model.opportunities.add(new CRMOpportunity({contact: this.model.get('id')}));
    },
    
    save: function(){
        var xhrs = [];
        this.$(".throbber").show();
        this.$("#save").prop('disabled', true);
        this.model.saving = true;
        xhrs.push(this.model.save(null, {
            success: function(){
                if(!this.isDialog){
                    this.saveOpportunities();
                }
                _.defer(function(){
                    this.model.saving = false;
                }.bind(this));
            }.bind(this),
            error: function(o, e){
                if(!this.isDialog){
                    this.$(".throbber").hide();
                    this.$("#save").prop('disabled', false);
                    clearAllMessages();
                    if(e.responseText != ""){
                        addError(e.responseText, true);
                    }
                    else{
                        addError("There was a problem saving the Contact", true);
                    }
                }
                _.defer(function(){
                    this.model.saving = false;
                }.bind(this));
            }.bind(this)
        }));
        return xhrs;
    },
    
    saveOpportunities: function(){
        var xhrs = [];
        this.model.opportunities.each(function(model){
            model.set('contact', this.model.get('id'));
            model.saving = true;
            if(!model.toDelete){
                xhrs.push(model.save(null, {
                    success: function(){
                        _.defer(function(){
                            model.saving = false;
                        }.bind(this));
                    },
                    error: function(){
                        _.defer(function(){
                            model.saving = false;
                        }.bind(this));
                    }
                }));
            }
            else if(!model.isNew()){
                // Delete as long as it isn't new (if it's new, and set for deletion, just do nothing)
                xhrs.push(model.destroy({wait: true}));
            }
        }.bind(this));
        if(!this.isDialog){
            $.when.apply(null, xhrs).done(function(){
                this.saveTasks();
            }.bind(this));
        }
        return xhrs;
    },
    
    saveTasks: function(){
        var xhrs = [];
        this.model.opportunities.each(function(model){
            model.tasks.each(function(task){
                task.set('opportunity', model.get('id'));
                task.saving = true;
                if(!task.toDelete){
                    // Create or Update
                    xhrs.push(task.save(null, {
                        success: function(){
                            _.defer(function(){
                                task.saving = false;
                            }.bind(this));
                        },
                        error: function(){
                            _.defer(function(){
                                task.saving = false;
                            }.bind(this));
                        }
                    }));
                }
                else if(!task.isNew()){
                    // Delete as long as it isn't new (if it's new, and set for deletion, just do nothing)
                    xhrs.push(task.destroy({wait: true}));
                }
            });
        });
        if(!this.isDialog){
            $.when.apply(null, xhrs).done(function(){
                this.$(".throbber").hide();
                this.$("#save").prop('disabled', false);
                clearAllMessages();
                document.location = this.model.get('url');
            }.bind(this));
        }
        return xhrs;
    },
       
    events: {
        "click #addOpportunity": "addOpportunity",
        "click #save": "save",
        "click #cancel": "cancel"
    },
    
    cancel: function(){
        document.location = this.model.get('url');
    },
    
    removeOpportunities: function(){
        _.each(this.subViews, function(view){
            if(view.model.toDelete){
                // To be deleted, remove from dom
                _.defer(function(){
                    view.$el.slideUp(200, view.remove.bind(view));
                }.bind(this));
            }
        }.bind(this));
    },
    
    updateOpportunities: function(){
        // Do deletions first
        this.removeOpportunities();
        // Now render the rest
        _.each(this.subViews, function(view){
            if(!view.model.toDelete){
                // Render
                view.render();
            }
        }.bind(this));
    },
        
    renderOpportunities: function(model){
        var view = new CRMOpportunityEditView({model: model});
        this.$("#opportunities").append(view.render());
        this.subViews.push(view);
    },
    
    render: function(){
        if(!this.model.saving){
            if(!this.isDialog){
                main.set('title', this.model.get('title'));
            }
            this.$el.html(this.template(this.model.toJSON()));
        }
        return this.$el;
    }

});
