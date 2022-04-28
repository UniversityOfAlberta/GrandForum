ActionPlan = Backbone.Model.extend({

    initialize: function(){
    },

    urlRoot: 'index.php?action=api.actionplan',

    getComponents: function(){
        var components = [];
        _.each(this.get('components'), function(val, comp){
            if(val == "1"){
                components.push(comp);
            }
        });
        return components;
    },

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
            components: {
                "A": "0",
                "V": "0",
                "O": "0",
                "I": "0",
                "D": "0",
                "S": "0",
                "F": "0"
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
