SubRoles = Backbone.Model.extend({

    initialize: function(){
        
    },

    urlRoot: function(){
        return 'index.php?action=api.person/' + this.get('userId') + '/subroles';
    },
    
    defaults: function(){
        return {
            userId: "",
            subroles: new Array()
        };
    }
});
