/**
 * Person Model
 */
Person = Backbone.Model.extend({

    initialize: function(){
        this.projects = new PersonProjects();
        this.projects.url = this.urlRoot + '/' + this.get('id') + '/projects';
        
        this.roles = new PersonRoles();
        this.roles.url = this.urlRoot + '/' + this.get('id') + '/roles';
        
        this.relations = new PersonRelations();
        this.relations.url = this.urlRoot + '/' + this.get('id') + '/relations';
        
        this.products = new PersonProducts();
        this.products.url = this.urlRoot + '/' + this.get('id') + '/products';
        
        this.privateProducts = new PersonProducts();
        this.privateProducts.url = this.urlRoot + '/' + this.get('id') + '/products/private';
        
        this.roleString = new PersonRoleString({id: this.get('id')});
        /*
        this.bind("sync", function(model, response, options){
            clearAllMessages();
            // if(options.changes.id == true){
            //     // Creation
            //     addSuccess("<i>" + this.get('name') + "</i> was created successfully");
            // }
            // else{
            //     // Update
            //     addSuccess("<i>" + this.get('name') + "</i> was updated successfully");
            // }
        });*/
        
        this.bind("error", function(e, response, options){
            clearAllMessages();
            addError(response.responseText);
        });
    },
    
    getLink: function(){
        return new Link({id: this.get('id'),
                         text: this.get('reversedName'),
                         url: this.get('url'),
                         target: ''});
                               
    },
    
    getProjects: function(){
        this.projects.fetch();
        return this.projects;
    },
    
    getRoles: function(){
        this.roles.fetch();
        return this.roles;
    },
    
    getRelations: function(){
        this.relations.fetch();
        return this.relations;
    },
    
    // Returns a simple string containing all of the roles for this Person
    // including leader etc.
    getRoleString: function(){
        this.roleString.fetch();
        return this.roleString;
    },
    
    getProducts: function(){
        this.products.fetch();
        return this.products;
    },
    
    getPrivateProducts: function(){
        this.privateProducts.fetch();
        return this.privateProducts;
    },
    
    isLoggedIn: function(){
        return (this.get('id') != 0);
    },
    
    getUniversityString: function(){
        var university = new Array();
        if(this.get('position') != ''){
            university.push(this.get('position'));
        }
        if(this.get('department') != ''){
            university.push(this.get('department'));
        }
        if(this.get('university') != ''){
            university.push(this.get('university'));
        }
        return university.join('<br />');
    },

    urlRoot: 'index.php?action=api.person',
    
    defaults: {
        id: null,
        name: '',
        realName: '',
        fullName: '',
        reversedName: '',
        email: '',
        nationality: '',
        gender: '',
        photo: wgServer + wgScriptPath + '/skins/face.png',
        cachedPhoto: wgServer + wgScriptPath + '/skins/face.png',
        twitter: '',
        university: '',
        position: '',
        department: '',
        publicProfile: '',
        privateProfile: '',
        url: ''
    }
});

/**
 * People Collection
 */
People = Backbone.Collection.extend({
    model: Person,
    
    url: function(){
        if(this.roles == undefined){
            return 'index.php?action=api.people';
        }
        return 'index.php?action=api.people/' + this.roles.join(',');
    }
});

/**
 * PersonProject RelationModel
 */
PersonProject = RelationModel.extend({
    initialize: function(){
        
    },

    urlRoot: function(){
        return 'index.php?action=api.person/' + this.get('personId') + '/projects'
    },
    
    getOwner: function(){
        var person = new Person({id: this.get('personId')});
        return person;
    },
    
    getTarget: function(){
        var project = new Project({id: this.get('projectId')});
        return project;
    },
    
    defaults: {
        id: null,
        personId: "",
        projectId: "",
        startDate: "",
        endDate: "",
        name: "",
        comment: "",
        deleted: false
    }
});

/**
 * PersonProjects RangeCollection
 */
PersonProjects = RangeCollection.extend({
    model: PersonProject,
    
    newModel: function(){
        return new Projects();
    },
});

/**
 * PersonRelation RelationModel
 */
PersonRelation = RelationModel.extend({
    initialize: function(){
        
    },

    urlRoot: function(){
        return 'index.php?action=api.person/' + this.get('personId') + '/relation'
    },
    
    getOwner: function(){
        var person = new Person({id: this.get('user1')});
        return person;
    },
    
    getTarget: function(){
        var person = new Person({id: this.get('user2')});
        return person;
    },
    
    defaults: {
        id: null,
        user1: "",
        user2: "",
        startDate: "",
        endDate: "",
        name: "",
        comment: "",
        deleted: false
    }
});

/**
 * PersonRelations RangeCollection
 */
PersonRelations = RangeCollection.extend({
    model: PersonRelation,
    
    newModel: function(){
        return new People();
    },
});

/**
 * Personrole RelationModel
 */
PersonRole = RelationModel.extend({
    initialize: function(){
    
    },
    
    urlRoot: function(){
        return 'index.php?action=api.person/' + this.get('personId') + '/roles'
    },
    
    getOwner: function(){
        var person = new Person({id: this.get('personId')});
        return person;
    },
    
    getTarget: function(){
        var role = new Role({id: parseInt(this.get('roleId'))});
        return role;
    },
    
    defaults: {
        personId: "",
        roleId: "",
        startDate: "",
        endDate: ""
    }
});

/**
 * PersonRoles RangeCollection
 */
PersonRoles = RangeCollection.extend({
    model: PersonRole,
    
    newModel: function(){
        return new Roles();
    },
});

/**
 * PersonProduct RelationModel
 */
PersonProduct = RelationModel.extend({
    initialize: function(){
    
    },
    
    urlRoot: function(){
        return 'index.php?action=api.person/' + this.get('personId') + '/products'
    },
    
    idAttribute: 'productId',
    
    getOwner: function(){
        var person = new Person({id: this.get('personId')});
        return person;
    },
    
    getTarget: function(){
        var product = new Product({id: this.get('productId')});
        return product;
    },
    
    defaults: {
        personId: "",
        productId: "",
        startDate: "",
        endDate: "",
    }
});

/**
 * PersonProducts RangeCollection
 */
PersonProducts = RangeCollection.extend({
    model: PersonProduct,
    
    multiUrl: function(){
        return 'index.php?action=api.product/';
    },
    
    newModel: function(){
        return new Products();
    },
});

/**
 * PersonRoleString Model
 */
PersonRoleString = Backbone.Model.extend({

    initialize: function(){
    
    },

    urlRoot: 'index.php?action=api.personRoleString',

    defaults: {
        id: "", // personId
        roleString: ""
    }

});
