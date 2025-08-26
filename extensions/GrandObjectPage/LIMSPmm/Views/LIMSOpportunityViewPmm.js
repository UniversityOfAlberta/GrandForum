LIMSOpportunityViewPmm = Backbone.View.extend({
    project: null,

    initialize: function(options){
        this.project = options.project;
        this.listenTo(this.model, "sync", this.render);
        this.listenTo(this.model.tasks, "sync", this.renderTasks);
        this.template = _.template($('#lims_opportunity_template').html());
    },
    
    renderTasks: function(){
        if(this.model.tasks.length > 0){
            this.$("#taskContainer").show();
        }
        this.$("#tasks > tbody").empty();
        this.model.tasks.each(function(model){
            var view = new LIMSTaskViewPmm({model: model, project: this.project});
            this.$("#tasks > tbody").append(view.render());
        }.bind(this));
    },
    
    render: function(){
        var templateData = this.model.toJSON();
    
        if (this.project) {
            templateData.project = this.project.toJSON();
        }
        this.$el.html(this.template(templateData));
        this.$el.addClass("opportunity");
        return this.$el;
    }

});
