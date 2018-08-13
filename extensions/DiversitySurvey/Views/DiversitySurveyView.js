DiversitySurveyView = Backbone.View.extend({

    initialize: function(){
        this.model.bind('change', this.render, this);
        this.template = _.template($('#diversity_template').html());
        _.defer(this.render);
    },
    
    events: {
        
    },
    
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        return this.$el;
    }

});
