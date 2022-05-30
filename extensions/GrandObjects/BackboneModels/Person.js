/**
 * Person Model
 */
Person = Backbone.Model.extend({

    initialize: function(){
        this.roles = new PersonRoles();
        this.roles.url = this.urlRoot + '/' + this.get('id') + '/roles';
        
        this.relations = new PersonRelations();
        this.relations.url = this.urlRoot + '/' + this.get('id') + '/relations';
        
        this.products = new PersonProducts();
        this.products.url = this.urlRoot + '/' + this.get('id') + '/products';
        
        this.universities = new PersonUniversities();
        this.universities.url = this.urlRoot + '/' + this.get('id') + '/universities';
        
        this.privateProducts = new PersonProducts();
        this.privateProducts.url = this.urlRoot + '/' + this.get('id') + '/products/private';
        
        this.adminProducts = new PersonProducts();
        this.adminProducts.url = this.urlRoot + '/' + this.get('id') + '/products/all';
        
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
            if(response.responseText != ''){
                clearAllMessages();
                addError(response.responseText);
            }
        });
    },
    
    getLink: function(){
        return new Link({id: this.get('id'),
                         text: this.get('reversedName'),
                         url: this.get('url'),
                         target: ''});
                               
    },
    
    getRoles: function(){
        this.roles.fetch();
        return this.roles;
    },
    
    getRelations: function(){
        this.relations.fetch();
        return this.relations;
    },
    
    getUniversities: function(){
        this.universities.fetch();
        return this.universities;
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
    
    getAdminProducts: function(){
        this.adminProducts.fetch();
        return this.adminProducts;
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
            var dept = this.get('department');
            if(this.get('researchArea') != ''){
                dept += "&nbsp;(" + this.get('researchArea') + ")";
            }
            university.push(dept);
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
        photo: wgServer + wgScriptPath + '/skins/face.png',
        cachedPhoto: wgServer + wgScriptPath + '/skins/face.png',
        twitter: '',
        website: '',
        ldap: '',
        googleScholar: '',
        sciverseId: '',
        orcId: '',
        wos: '',
        university: '',
        position: '',
        start: '',
        end: '',
        universities: new Array(),
        roles: new Array(),
        department: '',
        researchArea: '',
        publicProfile: '',
        privateProfile: '',
        profileStartDate: '',
        profileEndDate: '',
        url: ''
    }
});

/**
 * People Collection
 */
People = Backbone.Collection.extend({
    model: Person,
    
    roles: undefined,
    
    simple: false,
    
    url: function(){
        var url = 'index.php?action=api.people';
        if(this.roles != undefined){
            url += '/' + this.roles.join(',');
        }
        if(this.simple){
            url += '/simple';
        }
        return url;
    }
});

/**
 * PersonRelation RelationModel
 */
PersonRelation = RelationModel.extend({
    initialize: function(){
        
    },
    
    validate: function(attrs, options) {
        var regex = /^\d{4}-\d{2}-\d{2}/;
        if (attrs.startDate != '' && !regex.test(attrs.startDate)){
            return "Start Date is not valid";
        }
        if (attrs.endDate != '' && !regex.test(attrs.endDate)){
            return "End Date is not valid";
        }
    },

    urlRoot: function(){
        return 'index.php?action=api.person/' + this.get('user1') + '/relations'
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
        university: 0,
        personUniversity: null,
        type: "",
        status: "",
        thesis: "",
        startDate: new Date().toISOString().substr(0, 10),
        endDate: "",
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
        startDate: new Date().toISOString().substr(0, 10),
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
 * PersonUniversity RelationModel
 */
PersonUniversity = RelationModel.extend({
    initialize: function(){
    
    },
    
    validate: function(attrs, options) {
        var regex = /^\d{4}-\d{2}-\d{2}/;
        if (attrs.startDate != '' && !regex.test(attrs.startDate)){
            return "Start Date is not valid";
        }
        if (attrs.endDate != '' && !regex.test(attrs.endDate)){
            return "End Date is not valid";
        }
    },
    
    urlRoot: function(){
        return 'index.php?action=api.person/' + this.get('personId') + '/universities'
    },
    
    getOwner: function(){
        var person = new Person({id: this.get('personId')});
        return person;
    },
    
    getTarget: function(){
        var university = new University({id: parseInt(this.get('personUniversityId'))});
        return university;
    },
    
    defaults: {
        personId: "",
        univeristy: "",
        department: "",
        position: "",
        researchArea: "",
        primary: 0,
        personUniversityId: "",
        startDate: new Date().toISOString().substr(0, 10),
        endDate: ""
    }
});

/**
 * PersonUniversities RangeCollection
 */
PersonUniversities = RangeCollection.extend({
    model: PersonUniversity,
    
    newModel: function(){
        return new Universities();
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
