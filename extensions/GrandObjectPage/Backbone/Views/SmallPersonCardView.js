SmallPersonCardView = Backbone.View.extend({

    initialize: function(){
        this.model.bind('change', this.render, this);
        this.model.getRoles().bind('sync', this.renderRoles, this);
        this.template = _.template($("#small_person_card_template").html());
        this.model.fetch();
    },
    
    renderRoles: function(){
        var current = this.model.roles.getCurrent();
        var that = this;
        this.model.roles.ready().then(function(){
            var roles = Array();
            if(current.models.length > 0){
                _.each(current.models, function(role, index){
                    roles.push(role.get('name'));
                }, that);
                that.$el.find("#roles").html("(" + roles.join(', ') + ")");
            }
        });
    },

    render: function(){
        this.$el.css('width', '0');
        this.$el.html(this.template(this.model.toJSON()));
        this.renderRoles();
        return this.$el;
    }

});
