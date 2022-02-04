// Parent Classes
EliteProfile = Backbone.Model.extend({

    initialize: function(){
        
    },

    defaults: function(){
        return {
            id: "",
            user: null,
            pdf: "",
            created: "",
            region: "",
            status: "",
            comments: "",
            projects: [],
            otherProjects: [],
            matches: [],
            hires: [],
            hire: null,
            file: null
        };
    }
    
});

EliteProfiles = Backbone.Collection.extend({
    
    matched: false,
    
});

// Intern Classes
InternEliteProfile = EliteProfile.extend({

    urlRoot: 'index.php?action=api.eliteprofile/intern',
    
});

InternEliteProfiles = EliteProfiles.extend({
    
    model: InternEliteProfile,
    
    url: function(){
        if(this.matched){
            return 'index.php?action=api.eliteprofile/intern/matched';
        }
        else{
            return 'index.php?action=api.eliteprofile/intern';
        }
    }
    
});

// PhD Classes
PhDEliteProfile = EliteProfile.extend({

    urlRoot: 'index.php?action=api.eliteprofile/phd',
    
});

PhDEliteProfiles = EliteProfiles.extend({
    
    model: PhDEliteProfile,
    
    url: function(){
        if(this.matched){
            return 'index.php?action=api.eliteprofile/phd/matched';
        }
        else{
            return 'index.php?action=api.eliteprofile/phd';
        }
    }
    
});
