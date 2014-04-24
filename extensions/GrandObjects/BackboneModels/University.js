University = Backbone.Model.extend({

    initialize: function(){
        
    },

    urlRoot: 'index.php?action=api.university',

    defaults: {
        id: null,
        name: "",
        latitude: "",
        longitude: "",
        color: "",
        order: 10000,
        default: false
    }
});

Universities = Backbone.Collection.extend({
    
    model: University,
    
    url: 'index.php?action=api.university' 
    
});
