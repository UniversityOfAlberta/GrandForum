CRMTask = Backbone.Model.extend({

    initialize: function(){
    
    },

    urlRoot: function(){
        return 'index.php?action=api.crmtask';
    },

    defaults: function() {
        return{
            id: null,
            opportunity: "",
            description: "",
            dueDate: "",
            transactions: new Array(),
            status: ""
        };
    }

});
