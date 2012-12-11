Product = Backbone.Model.extend({
    initialize: function(){
        
    },
    
    urlRoot: 'index.php?action=api.product',
    
    defaults: {
        id : null,
        title: "",
        category: "",
        type: "",
        description: "",
        date: "",
        status: "",
        data: new Array(),
        lastModified: ""
    },
});
