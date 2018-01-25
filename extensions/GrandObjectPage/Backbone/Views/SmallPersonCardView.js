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
        var gotoUrl = this.model.get('url');
        if((this.model.get('sop_url') != "") && (_.findWhere(me.get('roles'), {role: CI}) == undefined)) { 
            gotoUrl = this.model.get('sop_url');
        }
        this.$el.html(this.template(_.extend({gotoUrl: gotoUrl}, this.model.toJSON())));
        this.renderRoles();
        return this.$el;
    }

});
