ElitePostingsView = PostingsView.extend({

    template: _.template($('#elitepostings_template').html()),
    
    initialize: function(){
        this.deleted = new ElitePostings();
        this.deleted.deleted = true;
        this.model.fetch();
        main.set('title', 'Project Proposals');
        this.listenTo(this.model, "sync", function(){
            this.deleted.fetch();
        }.bind(this));
        this.listenTo(this.model, "remove", function(){
            this.deleted.fetch();
        }.bind(this));
        this.listenTo(this.deleted, "sync", this.render);
    },
    
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        this.$("table#postings").DataTable({
            "autoWidth": true,
            "order": [[ 0, "desc" ]]
        });
        this.$("table#deleted").DataTable({
            "autoWidth": true,
            "order": [[ 0, "desc" ]]
        });
        this.$("#showDeleted").click(function(){
            this.$("#showDeleted").hide();
            this.$("#deletedPostings").slideDown();
        }.bind(this));
        return this.$el;
    }

});
