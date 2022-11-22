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
            type: ActionPlan.MANUAL,
            fitbit: {
                
            },
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

ActionPlan.FITBIT = "Fitbit Monitoring";
ActionPlan.MANUAL = "Manual-Monitoring";

ActionPlan.comp2Text = function(comp){
    switch(comp){
        case "A": 
            return "Activity";
        case "V":
            return "Vaccinate";
        case "O":
            return "Optimize Medication";
        case "I":
            return "Interact";
        case "D":
            return "Diet & Nutrition";
        case "S":
            return "Sleep";
        case "F":
            return "Falls Prevention";
    }
    return "Other";
}

ActionPlans = Backbone.Collection.extend({

    model: ActionPlan,
    
    getComponents: function(){
        var components = this.reduce(function(memo, model){
            return _.union(memo, model.getComponents());
        }, []);
        return components;
    },

    url: function(){
        return 'index.php?action=api.actionplan';
    }

});
