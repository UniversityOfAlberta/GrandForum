Project = Backbone.RelationalModel.extend({
    initialize: function(){
        this.bind("error", function(model, error){
            //addError(error);
        });
    },
    
    urlRoot: 'index.php?action=api.project',
    
    defaults: {
        id: null,
        name: '',
        fullname: '',
        description: '',
        status: '',
        type: '',
        deleted: ''
    }
});

Projects = Backbone.Collection.extend({
    model: Project,
    
    url: 'index.php?action=api.project'
});
