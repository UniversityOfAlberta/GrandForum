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
                other: "",
                decline: ""
            },
            racialized: '',
            immigration: '',
            comments: ''
        };
    },
    
});
