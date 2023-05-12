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
            time: "",
            when: "",
            goals: "",
            barriers: "",
            plan: "",
            dates: {
                "Mon": "0",
                "Tue": "0",
                "Wed": "0",
                "Thu": "0",
                "Fri": "0",
                "Sat": "0",
                "Sun": "0"
            },
            confidence: 0,
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
            return (wgLang == 'en') ? "Activity" : "Activité physique";
        case "V":
            return (wgLang == 'en') ? "Vaccinate" : "Vaccination";
        case "O":
            return (wgLang == 'en') ? "Optimize Medication" : "Optimisation des médicaments";
        case "I":
            return (wgLang == 'en') ? "Interact" : "Vie sociale";
        case "D":
            return (wgLang == 'en') ? "Diet & Nutrition" : "Alimentation";
        case "S":
            return (wgLang == 'en') ? "Sleep" : "Sommeil";
        case "F":
            return (wgLang == 'en') ? "Falls Prevention" : "Prévention des chutes";
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
