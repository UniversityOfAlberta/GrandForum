ElitePostingsView = PostingsView.extend({

    template: _.template($('#elitepostings_template').html()),
    
    initialize: function(){
        this.model.fetch();
        main.set('title', 'Project Proposals');
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
