LIMSOpportunityEditView = Backbone.View.extend({

    subViews: [],
    saving: false,

    initialize: function(){
        this.model.saving = false;
        this.listenTo(this.model, "sync", this.render);
        //this.listenTo(this.model.tasks, "sync", this.renderTasks);
        this.listenTo(this.model.tasks, "add", this.renderTasks);
        this.listenTo(this.model.tasks, "change:toDelete", this.removeTasks);
        this.listenTo(this.model, "change:category", this.updateTasks);
        this.selectTemplate();
    },
    
    selectTemplate: function(){
        if(!this.model.get('isAllowedToEdit')){
            // Not allowed to edit, use read-only version
            this.template = _.template($('#lims_opportunity_template').html());
        }
        else{
            // Use Edit version
            this.template = _.template($('#lims_opportunity_edit_template').html());
        }
    },
    
    addTask: function(){
        this.model.tasks.add(new LIMSTask({opportunity: this.model.get('id')}));
    },
    
    deleteOpportunity: function(){
        this.model.toDelete = true;
        this.model.trigger("change:toDelete");
    },
    
    events: {
        "click #deleteOpportunity": "deleteOpportunity",
        "click #addTask": "addTask"
    },
    
    removeTasks: function(){
        _.each(this.subViews, function(view){
            if(view.model.toDelete){
                // To be deleted, remove from dom
                view.remove();
            }
        }.bind(this));
    },
    
    updateTasks: function(){
        // Do deletions first
        this.removeTasks();
        // Now render the rest
        _.each(this.subViews, function(view){
            if(!view.model.toDelete){
                // Render
                view.render();
            }
        }.bind(this));
    },
    
    renderTasks: function(model){
        var view = new LIMSTaskEditView({model: model});
        this.$("#tasks > tbody").append(view.render());
        this.subViews.push(view);
    },
    
    render: function(){
        if(!this.model.saving){
            this.$el.html(this.template(this.model.toJSON()));
            this.$el.addClass("opportunity");
            this.$("#taskContainer").show();
            _.defer(function(){
                this.$('select[name=owner_id]').chosen();
            }.bind(this));
        }
        return this.$el;
    }

});
