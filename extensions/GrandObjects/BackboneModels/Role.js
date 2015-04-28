Role = Backbone.Model.extend({

    initialize: function(){

    },

    urlRoot: 'index.php?action=api.role',
    
    defaults: {
        id: null,
        userId: "",
        name: "",
        comment: "",
        startDate: "",
        endDate: "",
        deleted: false
    }
});

Roles = Backbone.Collection.extend({
    model: Role,
    
    url: 'index.php?action=api.role'
});
