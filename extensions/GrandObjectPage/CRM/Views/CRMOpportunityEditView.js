CRMOpportunityEditView = Backbone.View.extend({

    initialize: function(){
        this.listenTo(this.model, "sync", this.render);
        this.listenTo(this.model.tasks, "sync", this.renderTasks);
        this.listenTo(this.model.tasks, "add", this.renderTasks);
        this.listenTo(this.model.tasks, "remove", this.renderTasks);
        this.template = _.template($('#crm_opportunity_edit_template').html());
    },
    
    addTask: function(){
        this.model.tasks.add(new CRMTask({opportunity: this.model.get('id')}));
    },
    
    events: {
        "click #addTask": "addTask"
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
        this.$el.addClass("opportunity");
        this.renderTasks();
        return this.$el;
    }

});
