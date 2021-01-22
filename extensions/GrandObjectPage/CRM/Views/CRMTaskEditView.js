CRMTaskEditView = Backbone.View.extend({

    tagName: "li",

    initialize: function(){
        this.model.saving = false;
        this.listenTo(this.model, "sync", this.render);
        this.template = _.template($('#crm_task_edit_template').html());
    },
    
    events: {
        
    },
    
    render: function(){
        if(!this.model.saving){
            this.$el.html(this.template(this.model.toJSON()));
            _.defer(function(){
                this.$('select[name=assignee_id]').chosen();
            }.bind(this));
        }
        return this.$el;
    }

});
