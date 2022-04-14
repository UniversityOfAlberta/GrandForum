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
                "Mon": "0",
                "Tue": "0",
                "Wed": "0",
                "Thu": "0",
                "Fri": "0",
                "Sat": "0",
                "Sun": "0"
            },
            submitted: false,
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
