TagIt = Backbone.Model.extend({
    
    initialize: function(){
    
    },
    
    defaults: {
        name: 'tagit',
        capitalize: false,
        values: Array(),
        strictValues: true,
        suggestions: Array(),
        options: Array(),
    } 
    
});
