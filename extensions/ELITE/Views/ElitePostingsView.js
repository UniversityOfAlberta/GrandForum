ElitePostingsView = PostingsView.extend({

    template: _.template($('#elitepostings_template').html()),
    
    initialize: function(){
        this.model.fetch();
        main.set('title', 'Project Proposals');
        this.listenTo(this.model, "sync", this.render);
        this.listenTo(this.model, "remove", this.render);
    },
    
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        this.$("table#postings").DataTable({
            "autoWidth": true,
            "order": [[ 0, "desc" ]]
        });
        return this.$el;
    }

});
