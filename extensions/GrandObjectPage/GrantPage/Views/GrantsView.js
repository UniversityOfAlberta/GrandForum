GrantsView = Backbone.View.extend({

    initialize: function(){
        this.model.fetch();
        this.listenTo(this.model, 'sync', this.render);
        this.template = _.template($('#grants_template').html());
    },

    render: function(){
        main.set('title', "Grants");
        this.$el.html(this.template());
        main.trigger('change:title');
        this.$("#grants").dataTable({
            iDisplayLength: 100, 
            order: [
                [6, 'desc'],
                [5, 'desc']
            ]}
        );
        return this.$el;
    }

});
