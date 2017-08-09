GrantAwardsView = Backbone.View.extend({

    initialize: function(){
        this.model.fetch();
        this.listenTo(this.model, 'sync', this.render);
        this.template = _.template($('#grantawards_template').html());
    },

    render: function(){
        main.set('title', "Grant Awards");
        this.$el.html(this.template());
        this.$("#grantawards").dataTable({iDisplayLength: 100});
        return this.$el;
    }

});
