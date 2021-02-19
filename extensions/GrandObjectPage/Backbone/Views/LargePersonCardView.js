LargePersonCardView = Backbone.View.extend({

    lastWidth: 0,

    initialize: function(){
        this.model.bind('change', this.render, this);
        this.model.getRoleString().bind('sync', this.renderRoles, this);
        this.template = _.template($("#large_person_card_template").html());
    },
    
    renderRoles: function(){
        var current = this.model.roleString;
        this.$el.find("#roles").html("(" + this.model.roleString.get('roleString') + ")");
    },

    render: function(options){
        this.$el.html(this.template(this.model.toJSON()));
        return this.$el;
    }

});
