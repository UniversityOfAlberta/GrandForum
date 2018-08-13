Diversity = Backbone.Model.extend({
    
   initialize: function(){
        
   },
   
   defaults: function() {
        return {
            id: null,
            userId: '',
            reason: '',
            gender: new Array(),
            sexuality: new Array(),
            birth: '',
            indigenous: '',
            disability: '',
            disabilityVisibility: '',
            minority: '',
            race: new Array(),
            racialized: '',
            immigration: '',
            comments: ''
        };
    },
    
});
