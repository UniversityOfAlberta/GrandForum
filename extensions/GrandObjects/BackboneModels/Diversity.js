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
            decline: 0,
            reason: '',
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
            disability: '',
            disabilityVisibility: '',
            minority: '',
            race: {
                values: [],
                indigenousOther: "",
                other: "",
                decline: ""
            },
            racialized: '',
            immigration: '',
            comments: ''
        };
    },
    
});
