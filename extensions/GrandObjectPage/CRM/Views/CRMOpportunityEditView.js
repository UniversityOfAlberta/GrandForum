CRMOpportunityEditView = Backbone.View.extend({

    subViews: [],
    saving: false,

    initialize: function(){
        this.model.saving = false;
        this.listenTo(this.model, "sync", this.render);
        //this.listenTo(this.model.tasks, "sync", this.renderTasks);
        this.listenTo(this.model.tasks, "add", this.renderTasks);
        this.listenTo(this.model.tasks, "change:toDelete", this.updateTasks);
        this.listenTo(this.model, "change:category", this.updateTasks);
        this.template = _.template($('#crm_opportunity_edit_template').html());
    },
    
    addTask: function(){
        this.model.tasks.add(new CRMTask({opportunity: this.model.get('id')}));
    },
    
    deleteOpportunity: function(){
        this.model.toDelete = true;
        this.model.trigger("change:toDelete");
    },
    
    events: {
        "click #deleteOpportunity": "deleteOpportunity",
        "click #addTask": "addTask"
    },
    
    updateTasks: function(){
        _.each(this.subViews, function(view){
            if(view.model.toDelete){
                // To be deleted, remove from dom
                view.remove();
            }
            else{
                // Render
                view.render();
            }
        }.bind(this));
    },
    
    renderTasks: function(model){
        var view = new CRMTaskEditView({model: model});
        this.$("#tasks tbody").append(view.render());
        this.subViews.push(view);
    },
    
    render: function(){
        if(!this.model.saving){
            this.$el.html(this.template(this.model.toJSON()));
            this.$el.addClass("opportunity");
        }
        return this.$el;
    }

});
