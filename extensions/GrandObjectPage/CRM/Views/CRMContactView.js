CRMContactView = Backbone.View.extend({

    initialize: function(){
        this.model.fetch();
        this.listenTo(this.model, "sync", this.render);
        this.listenTo(this.model.opportunities, "sync", this.renderOpportunities);
        this.template = _.template($('#crm_contact_template').html());
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
            var view = new CRMOpportunityView({model: model});
            this.$("#opportunities").append(view.render());
        }.bind(this));
    },
    
    render: function(){
        main.set('title', this.model.get('title'));
        this.$el.html(this.template(this.model.toJSON()));
        return this.$el;
    }

});
