Role = Backbone.Model.extend({

    initialize: function(){
        this.set('projects', []);
    },

    urlRoot: 'index.php?action=api.role',
    
    defaults: {
        id: null,
        userId: "",
        name: "",
        title: "",
        comment: "",
        projects: null,
        startDate: new Date().toISOString().substr(0, 10),
        endDate: "",
        deleted: false
    }
});

Roles = Backbone.Collection.extend({
    model: Role,
    
    url: 'index.php?action=api.role'
});
