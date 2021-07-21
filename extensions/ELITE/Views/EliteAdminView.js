EliteAdminView = PostingsView.extend({

    template: _.template($('#elite_admin_template').html()),
    
    initialize: function(){
        main.set('title', 'ELITE Admin Panel');
        Backbone.Subviews.add(this);
    },
    
    subviewCreators: {
        "postings" : function() {
            var postings = new ElitePostings();
            return new ElitePostingsAdminView({model: postings});
        },
        "profiles": function(){
            var profiles = new EliteProfiles();
            return new EliteProfilesAdminView({model: profiles});
        }
    },
    
    render: function(){
        this.$el.html(this.template());
        return this.$el;
    }

});
