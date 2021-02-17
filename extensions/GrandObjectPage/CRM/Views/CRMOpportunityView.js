CRMOpportunityView = Backbone.View.extend({

    initialize: function(){
        this.listenTo(this.model, "sync", this.render);
        this.listenTo(this.model.tasks, "sync", this.renderTasks);
        this.template = _.template($('#crm_opportunity_template').html());
    },
    
    renderTasks: function(){
        this.$("#tasks > tbody").empty();
        this.model.tasks.each(function(model){
            var view = new CRMTaskView({model: model});
            this.$("#tasks > tbody").append(view.render());
        }.bind(this));
    },
    
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        this.$el.addClass("opportunity");
        return this.$el;
    }

});
