TagIt = Backbone.Model.extend({
    
    initialize: function(){
    
    },
    
    defaults: function() {
        return {
            name: 'tagit',
            capitalize: false,
            values: new Array(),
            strictValues: true,
            suggestions: new Array(),
            options: new Array()
        }
    } 
    
});
