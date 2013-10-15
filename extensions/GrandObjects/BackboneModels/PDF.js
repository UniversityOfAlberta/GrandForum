PDF = Backbone.Model.extend({

    initialize: function(){
        
    },

    urlRoot: 'index.php?action=api.pdf',
    
    defaults: {
        id: null,
        reportId: "",
        title: "",
        userId: "",
        generationUserId: "",
        submissionUserId: "",
        year: "",
        type: "",
        submitted: "",
        timestamp: "",
        url: ""
    }
});
