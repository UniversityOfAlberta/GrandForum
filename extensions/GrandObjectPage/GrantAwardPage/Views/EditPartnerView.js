EditPartnerView = Backbone.View.extend({

    parent: null,

    initialize: function(options){
        this.parent = options.parent;
        this.template = _.template($('#edit_partner_template').html());
    },
    
    deletePartner: function(){
        this.parent.model.get('partners').splice(_.indexOf(this.parent.model.get('partners'), this.model), 1);
        this.parent.renderPartners();
    },
    
    events: {
        "click #delete": "deletePartner"
    },
    
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        return this.$el;
    }

});
