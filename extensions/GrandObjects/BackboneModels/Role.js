Role = Backbone.Model.extend({

    initialize: function(){

    },

    urlRoot: 'index.php?action=api.role',
    
    defaults: {
        id: null,
        name: "",
        title: "",
        comment: "",
        startDate: "",
        endDate: ""
    }
});

Roles = Backbone.Collection.extend({
    model: Role,
    
    url: 'index.php?action=api.role'
});
