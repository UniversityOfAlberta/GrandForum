LIMSTaskView = Backbone.View.extend({

    tagName: "tr",

    initialize: function(){
        this.listenTo(this.model, "sync", this.render);
        this.template = _.template($('#lims_task_template').html());
    },
    
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        return this.$el;
    }

});
