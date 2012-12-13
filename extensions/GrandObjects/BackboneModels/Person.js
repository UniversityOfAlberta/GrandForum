Person = Backbone.Model.extend({

    initialize: function(){
        this.projects = new PersonProjects();
        this.projects.url = this.urlRoot + '/' + this.get('id') + '/projects';
        
        this.roles = new PersonRoles();
        this.roles.url = this.urlRoot + '/' + this.get('id') + '/roles';
        
        
        this.bind("sync", function(model, response, options){
            clearAllMessages();
            if(options.changes.id == true){
                // Creation
                addSuccess("<i>" + this.get('name') + "</i> was created successfully");
            }
            else{
                // Update
                addSuccess("<i>" + this.get('name') + "</i> was updated successfully");
            }
        });
        
        this.bind("error", function(e, response, options){
            clearAllMessages();
            addError(response.responseText);
        });
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

PersonRole = RelationModel.extend({
    initialize: function(){
    
    },
    
    urlRoot: function(){
        return 'index.php?action=api.person/' + this.personId + '/roles'
    },
    
    getOwner: function(){
        return people.get(this.get('personId'));
    },
    
    getTarget: function(){
        var role = new Role({id: parseInt(this.get('roleId'))});
        role.fetch();
        return role;
    },
    
    defaults: {
        personId: "",
        roleId: "",
        startDate: "",
        endDate: ""
    }
});

PersonRoles = RangeCollection.extend({
    model: PersonRole,
    
    newModel: function(){
        return new Roles();
    },
});
