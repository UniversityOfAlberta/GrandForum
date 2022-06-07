Diversity = Backbone.Model.extend({
    
   initialize: function(){
        
   },
   
   urlRoot: function(){
        return 'index.php?action=api.diversity';
   },
   
   defaults: function() {
        return {
            id: null,
            userId: '',
            language: 'en',
            decline: 0,
            reason: '',
            affiliation: '',
            gender: {
                values: [],
                other: "",
                decline: ""
            },
            orientation: {
                values: [],
                other: "",
                decline: ""
            },
            birth: '',
            indigenous: '',
            indigenousApply: {
                values: [],
                other: "",
                decline: ""
            },
            disability: '',
            disabilityVisibility: {
                values: [],
                other: "",
                decline: ""
            },
            minority: '',
            race: {
                values: [],
                other: "",
                decline: ""
            },
            trueSelf: '',
            valued: '',
            space: '',
            respected: {
                values: [],
                other: "",
                decline: ""
            },
            leastRespected: {
                values: [],
                other: "",
                decline: ""
            },
            principles: "",
            principlesDescribe: "",
            statement: "",
            improve: {
                values: [],
                other: "",
                decline: ""
            },
            training: "",
            preventsTraining: {
                values: [],
                other: "",
                decline: ""
            },
            trainingTaken: {
                values: [],
                other: "",
                decline: ""
            },
            implemented: "",
            stem: "",
            racialized: '',
            immigration: '',
            comments: ''
        };
    },
    
});
