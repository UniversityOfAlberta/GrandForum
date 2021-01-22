CRMOpportunityEditView = Backbone.View.extend({

    subViews: [],
    saving: false,

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
        _.each(this.subViews, function(view){
            view.remove();
        }.bind(this));
        this.subViews = new Array();
        this.$("#tasks").empty();
        this.model.tasks.each(function(model){
            var view = new CRMTaskEditView({model: model});
            this.$("#tasks").append(view.render());
            this.subViews.push(view);
        }.bind(this));
    },
    
    render: function(){
        console.log("RENDER OPPORTUNITY");
        this.$el.html(this.template(this.model.toJSON()));
        this.$el.addClass("opportunity");
        return this.$el;
    }

});
