Person = Backbone.Model.extend({

    initialize: function(){
        this.projects = new PersonProjects();
        this.projects.url = this.urlRoot + '/' + this.get('id') + '/projects';
        
        this.roles = new PersonRoles();
        this.roles.url = this.urlRoot + '/' + this.get('id') + '/roles';
        
        this.products = new PersonProducts();
        this.products.url = this.urlRoot + '/' + this.get('id') + '/products';
        
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
    
    getProjects: function(){
        this.projects.fetch();
        return this.projects;
    },
    
    getRoles: function(){
        this.roles.fetch();
        return this.roles;
    },
    
    getProducts: function(){
        this.products.fetch();
        return this.products;
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
        person = new Person({id: this.get('personId')});
        person.fetch();
        return person;
    },
    
    getTarget: function(){
        project = new Project({id: this.get('projectId')});
        project.fetch();
        return project;
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
        person = new Person({id: this.get('personId')});
        person.fetch();
        return person;
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

PersonProduct = RelationModel.extend({
    initialize: function(){
    
    },
    
    urlRoot: function(){
        return 'index.php?action=api.person/' + this.personId + '/products'
    },
    
    getOwner: function(){
        person = new Person({id: this.get('personId')});
        person.fetch();
        return person;
    },
    
    getTarget: function(){
        var product = new Product({id: this.get('productId')});
        product.fetch();
        return product;
    },
    
    defaults: {
        personId: "",
        productId: ""
    }
});

PersonProducts = Backbone.Collection.extend({
    model: PersonProduct,
});
