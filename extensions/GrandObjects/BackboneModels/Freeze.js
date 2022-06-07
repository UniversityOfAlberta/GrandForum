Freeze = Backbone.Model.extend({

    initialize: function(){
    },

    urlRoot: 'index.php?action=api.freeze',

    defaults: function() {
        return {
            id: null,
            projectId: "",
            feature: ""
        };
    }

});

Freeze.features = ['Description', 'Schedule/Milestones', 'Budget', 'Projections', 'EDI'];
Freeze.globalFeatures = ['Manage People', 'Leverages', 'Collaborations', 'Publications'];

Freezes = Backbone.Collection.extend({

    model: Freeze,

    url: function(){
        return 'index.php?action=api.freeze';
    }

});
