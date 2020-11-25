CRMTaskEditView = Backbone.View.extend({

    tagName: "li",

    initialize: function(){
        this.listenTo(this.model, "sync", this.render);
        this.template = _.template($('#crm_task_edit_template').html());
    },
    
    events: {
        
    },
    
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        return this.$el;
    }

});
