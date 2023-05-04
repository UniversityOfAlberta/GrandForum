ActionPlanTrackerView = Backbone.View.extend({

    template: _.template($('#action_plan_tracker_template').html()),

    initialize: function() {
        this.render();
        this.model.bind('change:tracker', this.save.bind(this));
    },

    events: {
        
    },
    
    save: function(){
        this.model.save();
    },

    render: function (){
        this.$el.html(this.template(this.model.toJSON()));
        return this.$el;
    }

});
