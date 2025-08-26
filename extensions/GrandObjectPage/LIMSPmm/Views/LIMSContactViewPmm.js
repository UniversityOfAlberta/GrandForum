LIMSContactViewPmm = Backbone.View.extend({
    isDialog: false,
    project: null,

    initialize: function(options){
        this.model.fetch();
        this.listenTo(this.model, "sync", this.render);
        this.listenTo(this.model.opportunities, "sync", this.renderOpportunities);
        this.template = _.template($('#lims_contact_template').html());

        var projectId = this.model.get('projectId');

        if (projectId) {
            this.project = new Project({ id: projectId });
            this.project.fetch();
            this.project.getMembers();
            this.listenTo(this.project, 'sync', this.render);
            this.listenTo(this.project.members, 'sync', this.render);
        }

        if(options.isDialog != undefined){
            this.isDialog = options.isDialog;
        }
    },
       
    events: {
        "click #edit": "edit"
    },
    
    edit: function(){
        document.location = document.location + '/edit';
    },
    
    renderOpportunities: function(){
        this.$("#opportunities").empty();
        this.model.opportunities.each(function(model){
            var view = new LIMSOpportunityViewPmm({model: model, project: this.project});
            this.$("#opportunities").append(view.render());
        }.bind(this));
    },
    
    render: function(){
        if (!_.isUndefined(main) && !_.isUndefined(main.set)) {
            main.set('title', this.model.get('title'));
        }
        this.$el.html(this.template(this.model.toJSON()));
        return this.$el;
        
    }

});
