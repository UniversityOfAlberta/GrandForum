ActionPlanView = Backbone.View.extend({

    template: _.template($('#action_plan_template').html()),

    initialize: function() {
        this.model.bind('sync', this.render);
        this.model.fetch();
    },

    events: {

    },

    render: function () {
        this.$el.html(this.template(this.model.toJSON()));     
        return this.$el;
    }

});
