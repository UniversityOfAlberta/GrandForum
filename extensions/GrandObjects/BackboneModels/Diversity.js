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
                value: '',
                woman: '',
                man: '',
                other: '',
                decline: ''
            },
            orientation: {
                value: '',
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
                value: "",
                values: [],
                other: "",
                decline: "",
                decline2: ""
            },
            languageMinority: {
                value: "",
                yes: "",
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
            immigration: {
                values: [],
                other: "",
                decline: ""
            },
            comments: ''
        };
    },
    
});
