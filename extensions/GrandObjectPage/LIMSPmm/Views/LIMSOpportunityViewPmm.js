LIMSOpportunityViewPmm = Backbone.View.extend({
    project: null,

    emailNotificationView: null,

    events: {},

    initialize: function (options) {
        this.project = options.project;
        this.listenTo(this.model, "sync", this.render);
        this.listenTo(this.model.tasks, "sync", this.renderTasks);

        var userRole = _.pluck(_.filter(me.get('roles'), function(el){return el.title == this.project.get("name") ||  el.role !== PL}.bind(this)), 'role');
        var isPLAllowed = _.intersection(userRole, [PL, STAFF, MANAGER, ADMIN]).length > 0 ;
            
        this.model.set('isLeaderAllowedToEdit', isPLAllowed);

        this.template = _.template($('#lims_opportunity_template').html());
        this.emailNotificationView = new LIMSEmailNotificationViewPmm({
            opportunity: this.model,
            project: this.project
        });
    },

    renderTasks: function () {
        if (this.model.tasks.length > 0) {
            this.$("#taskContainer").show();
        }
        this.$("#tasks > tbody").empty();
        this.model.tasks.each(function (model) {
            var view = new LIMSTaskViewPmm({ model: model, project: this.project });
            this.$("#tasks > tbody").append(view.render());
        }.bind(this));
    },

    render: function () {
        var templateData = this.model.toJSON();
        this.$el.html(this.template(templateData));
        
        this.emailNotificationView.setElement(this.$('#emailAccordion')).render();
        
        this.$el.addClass("opportunity");
        return this.$el;
    }

});