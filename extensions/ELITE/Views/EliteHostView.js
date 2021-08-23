EliteHostView = PostingsView.extend({

    template: _.template($('#elite_host_template').html()),
    
    initialize: function(){
        main.set('title', 'ELITE Host Panel');
        Backbone.Subviews.add(this);
    },
    
    subviewCreators: {
        "postings" : function() {
            var postings = new ElitePostings();
            return new EliteHostPostingsView({model: postings});
        },
        "profiles": function(){
            var profiles = new EliteProfiles();
            profiles.matched = true;
            return new EliteHostProfilesView({model: profiles});
        }
    },
    
    render: function(){
        this.$el.html(this.template());
        return this.$el;
    }

});

EliteHostPostingsView = PostingsView.extend({

    template: _.template($('#elite_host_postings_template').html()),
    
    initialize: function(){
        this.model.fetch();
        this.listenTo(this.model, "sync", this.render);
        this.listenTo(this.model, "remove", this.render);
    },
    
    clone: function(el){
        var target = el.currentTarget;
        var id = $(target).attr("id");
        var copy = new ElitePosting(this.model.get(id).toJSON());
        copy.set('id', null);
        copy.set('visibility', 'Submitted');
        copy.set('comments', "");
        copy.save(null, {
            success: function(){
                clearSuccess();
                clearError();
                addSuccess('The project <i>' + copy.get('title') + '</i> was duplicated');
                this.model.fetch();
            }.bind(this),
            error: function(){
                clearSuccess();
                clearError();
                addError('There was a problem duplicating the project <i>' + copy.get('title') + '</i>');
            }.bind(this)
        });
    },
    
    events:  {
        "click .copy-icon": "clone",
        "click .delete-icon": "delete",
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

EliteHostProfilesView = PostingsView.extend({

    template: _.template($('#elite_host_profiles_template').html()),
    
    initialize: function(){
        this.model.fetch();
        this.listenTo(this.model, "sync", this.render);
        this.listenTo(this.model, "remove", this.render);
    },
    
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        this.$("table#profiles").DataTable({
            "autoWidth": true,
            "order": [[ 0, "desc" ]]
        });
        return this.$el;
    }

});
