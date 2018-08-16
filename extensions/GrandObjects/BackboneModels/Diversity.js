Diversity = Backbone.Model.extend({
    
   initialize: function(){
        
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
            sexuality: {
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
