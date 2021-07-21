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
        };
    }
    
});

EliteProfiles = Backbone.Collection.extend({
    
    model: EliteProfile,
    
    url: function(){
        return 'index.php?action=api.eliteprofile';
    }
    
});
