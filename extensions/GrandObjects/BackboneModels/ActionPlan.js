ActionPlan = Backbone.Model.extend({

    initialize: function(){
    },

    urlRoot: 'index.php?action=api.actionplan',

    defaults: function() {
        return {
            id: null,
            userId: "",
            date: "",
            goals: "",
            barriers: "",
            plan: "",
            tracker: {
                "Mon": false,
                "Tue": false,
                "Wed": false,
                "Thu": false,
                "Fri": false,
                "Sat": false,
                "Sun": false
            },
            created: ""
        };
    }

});

ActionPlans = Backbone.Collection.extend({

    model: ActionPlan,

    url: function(){
        return 'index.php?action=api.actionplan';
    }

});
