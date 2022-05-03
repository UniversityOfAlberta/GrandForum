ActionPlanHistoryView = Backbone.View.extend({

    template: _.template($('#action_plan_history_template').html()),

    initialize: function() {
        this.model.bind('sync', this.render.bind(this));
        this.render();
    },

    events: {
        "click .viewActionPlan": "viewActionPlan"
    },
    
    viewActionPlan: function(e){
        var id = $(e.currentTarget).attr('data-id');
        var viewActionPlan = new ActionPlanView({model: actionPlans.get(id), el: $('#viewActionPlanDialog')});
    },

    render: function (){
        this.$el.html(this.template(this.model.where({submitted: true})));
        return this.$el;
    }

});
