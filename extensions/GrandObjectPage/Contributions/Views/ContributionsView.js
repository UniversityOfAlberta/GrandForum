ContributionsView = Backbone.View.extend({

    initialize: function(){
        this.model.fetch();
        this.listenTo(this.model, 'sync', this.render);
        this.template = _.template($('#contributions_template').html());
    },

    render: function(){
        main.set('title', "Contributions");
        this.$el.html(this.template());
        this.$("#contributions").dataTable({
            'iDisplayLength': 100,
            'dom': 'Blfrtip',
            'buttons': [
                'excel', 'pdf'
            ]
        });
        return this.$el;
    }

});
