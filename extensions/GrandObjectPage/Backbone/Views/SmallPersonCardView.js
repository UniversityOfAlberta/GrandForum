SmallPersonCardView = Backbone.View.extend({

    initialize: function(){
        this.model.bind('change', this.render, this);
        this.template = _.template($("#small_person_card_template").html());
        this.model.fetch();
        this.$el.css('display', 'none');
    },
    
    renderRoles: function(){
        var roles = Array();
        _.each(this.model.get('roles'), function(role, index){
            roles.push(role.role);
        });
        this.$el.find("#roles").html("(" + roles.join(', ') + ")");
        if(!this.$el.is(":animated")){
            this.$el.css('display', 'block');
        }
    },

    render: function(options){
        this.$el.html(this.template(this.model.toJSON()));
        return this.$el;
    }

});
