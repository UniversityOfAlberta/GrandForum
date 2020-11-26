CRMOpportunityEditView = Backbone.View.extend({

    initialize: function(){
        this.listenTo(this.model, "sync", this.render);
        this.listenTo(this.model.tasks, "sync", this.renderTasks);
        this.template = _.template($('#crm_opportunity_edit_template').html());
    },
    
    events: {
        
    },
    
    renderTasks: function(){
        this.$("#tasks").empty();
        this.model.tasks.each(function(model){
            var view = new CRMTaskEditView({model: model});
            this.$("#tasks").append(view.render());
        }.bind(this));
    },
    
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        this.renderTasks();
        return this.$el;
    }

});
