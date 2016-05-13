LargePersonCardView = Backbone.View.extend({

    initialize: function(){
        this.model.bind('change', this.render, this);
        this.model.getRoleString().bind('sync', this.renderRoles, this);
        this.template = _.template($("#large_person_card_template").html());
        setInterval($.proxy(function(){
            this.responsive();
        }, this), 33);
    },
    
    renderRoles: function(){
        var current = this.model.roleString;
        this.$el.find("#roles").html("(" + this.model.roleString.get('roleString') + ")");
    },
    
    responsive: function(){
        if(this.$el.parent().width() <= 400){
            var contact = this.$("div.card_description > #contact").detach();
            this.$el.append(contact);
        }
        else{
            var contact = this.$("#contact").detach();
            this.$("div.card_description").append(contact);
        }
    },

    render: function(options){
        this.$el.html(this.template(this.model.toJSON()));
        return this.$el;
    }

});
