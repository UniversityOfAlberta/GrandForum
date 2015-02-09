PersonEditView = Backbone.View.extend({

    initialize: function(){
        this.model.bind('change', this.render, this);
        this.template = _.template($("#person_edit_template").html());
    },
    
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        return this.$el;
    }

});
