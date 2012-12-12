Person = Backbone.RelationalModel.extend({
    initialize: function(){
        this.get('projects').url = this.urlRoot + '/' + this.get('id') + '/projects';
        this.bind('change:id', function(){
            this.get('projects').url = this.urlRoot + '/' + this.get('id') + '/projects';
        });
    },
    
    relations: [{
        type: Backbone.HasMany,
        key: 'projects',
        relatedModel: 'PersonProject',
        collectionType: 'PersonProjects'
    },
    /*{
        type: Backbone.HasMany,
        key: 'products',
        relatedModel: 'PersonProduct',
        reverseRelation: {
            key: 'product'
        }
    }*/],
    
    urlRoot: 'index.php?action=api.person',
    
    defaults: {
        id: null,
        name: '',
        realName: '',
        reversedName: '',
        email: '',
        nationality: '',
        gender: '',
        photo: '',
        twitter: '',
        university: '',
        position: '',
        department: '',
        publicProfile: '',
        privateProfile: ''
    }
});

PersonProject = Backbone.RelationalModel.extend({
    
    initialize: function(){

    },
    
    relations: [{
        type: Backbone.HasOne,
        key: 'project',
        relatedModel: 'Project'
    },
    {
        type: Backbone.HasOne,
        key: 'person',
        relatedModel: 'Person'
    }],

    urlRoot: function(){
        return 'index.php?action=api.person/' + this.personId + '/projects'
    },
    
    defaults: {
        projectId: "",
        personId: "",
        startDate: "",
        endDate: ""
    }
});

People = Backbone.Collection.extend({
    model: Person,
    
    url: 'index.php?action=api.person'
});

PersonProjects = Backbone.Collection.extend({
    model: PersonProject,
});
