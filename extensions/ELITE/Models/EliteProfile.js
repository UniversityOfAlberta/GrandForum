EliteProfile = Backbone.Model.extend({

    initialize: function(){
        
    },

    urlRoot: 'index.php?action=api.eliteprofile',

    defaults: function(){
        return {
            id: "",
            user: null,
            pdf: "",
            created: "",
            status: "",
            comments: "",
            projects: [],
            matches: [],
        };
    }
    
});

EliteProfiles = Backbone.Collection.extend({
    
    model: EliteProfile,
    
    matched: false,
    
    url: function(){
        if(this.matched){
            return 'index.php?action=api.eliteprofile/matched';
        }
        else{
            return 'index.php?action=api.eliteprofile';
        }
    }
    
});
