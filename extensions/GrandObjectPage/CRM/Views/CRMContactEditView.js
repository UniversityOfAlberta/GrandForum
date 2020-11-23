CRMContactEditView = Backbone.View.extend({

    initialize: function(){
        this.model.fetch();
        this.listenTo(this.model, "sync", this.render);
        this.listenTo(this.model, "change:title", function(){
            main.set('title', this.model.get('title'));
        });
        this.template = _.template($('#crm_contact_edit_template').html());
    },
       
    events: {
        
    },
    
    render: function(){
        main.set('title', this.model.get('title'));
        this.$el.html(this.template(this.model.toJSON()));
        return this.$el;
    }

});
