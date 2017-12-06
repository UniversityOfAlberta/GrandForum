Contribution = Backbone.Model.extend({

    initialize: function(){
    
    },

    url: function(){
        if(this.get('revId') != ""){
            return 'index.php?action=api.contribution/' + this.get('id') + '/' + this.get('revId');
        }
        else{
            return 'index.php?action=api.contribution/' + this.get('id');
        }
    },

    defaults: function() {
        return{
            id: null,
            revId: "",
            name: "",
            start: "",
            end: "",
            authors: new Array(),
            partners: new Array(),
            cash: "",
            inkind: "",
            total: ""
        };
    }

});

Contributions = Backbone.Collection.extend({

    model: Contribution,

    url: function(){
        return 'index.php?action=api.contribution';
    }

});
