WikiPage = Backbone.Model.extend({

    initialize: function(){
        
    },

    urlRoot: 'index.php?action=api.wikipage',
    
    defaults: {
        id: null,
        ns: '',
        title: '',
        url: '',
        text: ''
    }
});
