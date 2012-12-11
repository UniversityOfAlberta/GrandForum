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
        collectionType: 'PersonProjects',
        reverseRelation: {
            key: 'person'
        }
    }],

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

    urlRoot: function(){
        return 'index.php?action=api.person/' + this.personId + '/projects'
    },
    
    getProject: function(){
        return projects.get(this.get('projectId'));
    },
    
    defaults: {
        projectId: "",
        startDate: "",
        endDate: ""
    }
});

PersonProjects = Backbone.Collection.extend({
    model: PersonProject,
    
    /**
     * Returns a collection of Projects which the Person is currently in
     */
    getProjects: function(){
        var now = new Date();
        var date = Date.format(now, 'yyyy-MM-dd HH:mm:ss');
        return this.getProjectsDuring(date, '5000');  
    },
    
    /**
     * Returns a collection of Project which fall between startDate and endDate
     */
    getProjectsDuring: function(startDate, endDate){
        personProjects = _.filter(this.models, function(personProject){ 
            return between(personProject, startDate, endDate);
        });
        
        projectsDuring = new Projects();
        _.each(personProjects, function(personProject){
            projectsDuring.add(personProject.getProject());
        });
        return projectsDuring;
    }
});

People = Backbone.Collection.extend({
    model: Person,
    
    url: 'index.php?action=api.person'
});
