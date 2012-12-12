Person = Backbone.Model.extend({

    initialize: function(){
        this.projects = new PersonProjects();
        this.projects.url = this.urlRoot + '/' + this.get('id') + '/projects';
    },

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

People = Backbone.Collection.extend({
    model: Person,
    
    url: 'index.php?action=api.person'
});

PersonProject = RelationModel.extend({
    initialize: function(){
        
    },

    urlRoot: function(){
        return 'index.php?action=api.person/' + this.personId + '/projects'
    },
    
    getOwner: function(){
        return people.get(this.get('personId'));
    },
    
    getTarget: function(){
        return projects.get(this.get('projectId'));
    },
    
    defaults: {
        personId: "",
        projectId: "",
        startDate: "",
        endDate: ""
    }
});

PersonProjects = RangeCollection.extend({
    model: PersonProject,
    
    newModel: function(){
        return new Projects();
    },
});
