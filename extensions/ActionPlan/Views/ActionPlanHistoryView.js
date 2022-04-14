ActionPlanHistoryView = Backbone.View.extend({

    template: _.template($('#action_plan_history_template').html()),

    initialize: function() {
        this.model.bind('sync', this.render.bind(this));
        this.render();
    },

    events: {
        
    },

    render: function (){
        this.$el.html(this.template(this.model.toJSON()));
        return this.$el;
    }

});
