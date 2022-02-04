EliteHostView = PostingsView.extend({
    
    type: "Intern",
    
    initialize: function(options){
        if(options.type != undefined){
            this.type = options.type;
        }
        if(this.type == "Intern"){
            if(wgLang == 'en'){
                main.set('title', 'ELITE Internship Host Panel');
            }
            else{
                main.set('title', 'Panneau pour les responsables de stage ELITE');
            }
            this.template = _.template($('#elite_host_template').html());
        }
        else if (this.type == "PhD"){
            if(wgLang == 'en'){
                main.set('title', 'PhD Fellowship Supervisor Panel');
            }
            else{
                main.set('title', 'Panneau pour les superviseur-e-s des candidat-e-s de bourses doctorales');
            }
            this.template = _.template($('#elite_phd_template').html());
        }
        Backbone.Subviews.add(this);
    },
    
    subviewCreators: {
        "postings" : function() {
            var postings = new ElitePostings();
            postings.type = this.type;
            return new EliteHostPostingsView({model: postings});
        },
        "intern_profiles": function(){
            var profiles = new InternEliteProfiles();
            profiles.matched = true;
            return new EliteHostProfilesView({model: profiles});
        },
        "phd_profiles": function(){
            var profiles = new PhDEliteProfiles();
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
    acceptDialog: null,
    rejectDialog: null,
    
    initialize: function(){
        this.model.fetch();
        this.listenTo(this.model, "sync", this.render);
        this.listenTo(this.model, "remove", this.render);
    },
    
    openAcceptDialog: function(el){
        var id = $(el.target).attr('data-id');
        var match = $(el.target).attr('data-match');
        this.acceptDialog.dialog('open');
        this.acceptDialog.model = this.model.get(id);
        this.acceptDialog.model.set('hire', {'match': match, 'action': 'Accepted'});
    },
    
    openRejectDialog: function(el){
        var id = $(el.target).attr('data-id');
        var match = $(el.target).attr('data-match');
        this.rejectDialog.dialog('open');
        this.rejectDialog.model = this.model.get(id);
        this.rejectDialog.model.set('hire', {'match': match, 'action': 'Rejected'});
    },
    
    events: {
        "click .reject": "openRejectDialog",
        "click .accept": "openAcceptDialog",
    },
    
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        this.$("table#profiles").DataTable({
            "autoWidth": true,
            "order": [[ 0, "desc" ]]
        });
        this.rejectDialog = this.$("#rejectDialog").dialog({
            autoOpen: false,
            modal: true,
            show: 'fade',
            resizable: false,
            draggable: false,
            buttons: {
                "Reject": function(){
                    this.rejectDialog.model.save();
                    this.rejectDialog.dialog('close'); 
                }.bind(this),
                "Cancel": function(){
                    this.rejectDialog.dialog('close');
                }.bind(this)
            }
        });
        
        this.acceptDialog = this.$("#acceptDialog").dialog({
            autoOpen: false,
            modal: true,
            show: 'fade',
            resizable: false,
            draggable: false,
            buttons: {
                "Accept": function(){
                    this.acceptDialog.model.save();
                    this.acceptDialog.dialog('close'); 
                }.bind(this),
                "Cancel": function(){
                    this.acceptDialog.dialog('close');
                }.bind(this)
            }
        });
        return this.$el;
    }

});
