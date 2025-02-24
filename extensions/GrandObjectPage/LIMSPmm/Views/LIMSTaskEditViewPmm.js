LIMSTaskEditViewPmm = Backbone.View.extend({

    tagName: "tr",

    project: null,

    initialize: function(options){
        this.project = options.project;
        this.model.saving = false;
        this.listenTo(this.model, "sync", this.render);
        this.selectTemplate();

    },
    
    selectTemplate: function(){
        if(!this.model.get('isAllowedToEdit')){
            // Not allowed to edit, use read-only version
            this.template = _.template($('#lims_task_template').html());
        }
        else{
            // Use Edit version
            this.template = _.template($('#lims_task_edit_template').html());
        }
    },
    
    events: {
        "click #deleteTask": "deleteTask"
    },
    
    deleteTask: function(){
        this.model.toDelete = true;
        this.model.trigger("change:toDelete");
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
