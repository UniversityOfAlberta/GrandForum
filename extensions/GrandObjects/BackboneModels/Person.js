/**
 * Person Model
 */
Person = Backbone.Model.extend({

    initialize: function(){
        this.projects = new PersonProjects();
        this.projects.url = this.urlRoot + '/' + this.get('id') + '/projects';
        
        this.themes = new PersonThemes();
        this.themes.url = this.urlRoot + '/' + this.get('id') + '/themes';
        
        this.roles = new PersonRoles();
        this.roles.url = this.urlRoot + '/' + this.get('id') + '/roles';
        
        this.subRoles = new SubRoles({userId: this.get('id')});
        
        this.relations = new PersonRelations();
        this.relations.url = this.urlRoot + '/' + this.get('id') + '/relations';
        
        this.products = new PersonProducts();
        this.products.url = this.urlRoot + '/' + this.get('id') + '/products';
        
        this.universities = new PersonUniversities();
        this.universities.url = this.urlRoot + '/' + this.get('id') + '/universities';
        
        this.alumni = new Alumni();
        this.alumni.personId = this.get('id');
        
        this.privateProducts = new PersonProducts();
        this.privateProducts.url = this.urlRoot + '/' + this.get('id') + '/products/private';
        
        this.managedProducts = new PersonProducts();
        this.managedProducts.url = this.urlRoot + '/' + this.get('id') + '/products/managed';
        
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
            clearAllMessages();
            addError(response.responseText);
        });
    },
    
    isBoardMod: function(){
        var isMod = false;
        _.each(boardMods, function(r){
            if(_.findWhere(me.get('roles'), {role: r}) != undefined){
                isMod = true;
            }
        });
        return isMod;
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
    
    getThemes: function(){
        this.themes.fetch();
        return this.themes;
    },
    
    getRoles: function(){
        this.roles.fetch();
        return this.roles;
    },
    
    getSubRoles: function(){
        this.subRoles.fetch();
        return this.subRoles;
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
    
    getManagedProducts: function(){
        this.managedProducts.fetch();
        return this.managedProducts;
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
        if(this.get('position') != "" || this.get('stakeholder') != ""){
            if(this.get('position') != "" && this.get('stakeholder') != ""){
                university.push(this.get('stakeholder') + "/" + this.get('position'));
            }
            else if(this.get('stakeholder') != ""){
                university.push(this.get('stakeholder'));
            }
            else if(this.get('position') != ""){
                university.push(this.get('position'));
            }
        }
        if(this.get('department') != ''){
            var deptStr = this.get('department');
            if(this.get('faculty') != ''){
                deptStr += " / " + this.get('faculty');
            }
            university.push(deptStr);
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
        stakeholder: '',
        gender: '',
        birthDate: '',
        indigenousStatus: '',
        minorityStatus: '',
        disabilityStatus: '',
        photo: wgServer + wgScriptPath + '/skins/face.png',
        cachedPhoto: wgServer + wgScriptPath + '/skins/face.png',
        twitter: '',
        website: '',
        linkedin: '',
        googleScholar: '',
        orcid: '',
        scopus: '',
        researcherId: '',
        office: '',
        university: '',
        position: '',
        roles: new Array(),
        department: '',
        faculty: '',
        keywords: '',
        publicProfile: '',
        privateProfile: '',
        extra: {},
        url: '',
        candidate: false
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
        startDate: new Date().toISOString().substr(0, 10),
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
 * PersonThemesLeadership RelationModel
 */
PersonTheme = RelationModel.extend({
    initialize: function(){
        
    },

    urlRoot: function(){
        return 'index.php?action=api.person/' + this.get('personId') + '/themes'
    },
    
    getOwner: function(){
        var person = new Person({id: this.get('personId')});
        return person;
    },
    
    getTarget: function(){
        return null;
    },
    
    defaults: {
        id: null,
        personId: "",
        themeId: "",
        coLead: 'False',
        coordinator: 'False',
        startDate: new Date().toISOString().substr(0, 10),
        endDate: "",
        name: "",
        comment: "",
        deleted: false
    }
});

/**
 * PersonThemes RangeCollection
 */
PersonThemes = RangeCollection.extend({
    model: PersonTheme,
    
    newModel: function(){
        return null;
    },
});

/**
 * PersonRelation RelationModel
 */
PersonRelation = RelationModel.extend({
    initialize: function(){
        
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
    
    defaults: function(){
        return {
            id: null,
            user1: "",
            user2: "",
            startDate: new Date().toISOString().substr(0, 10),
            endDate: "",
            projects: new Array(),
            name: "",
            comment: "",
            deleted: false
        }
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
        role: "",
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
        var dSplit = this.get('department').split(' / ', 2);
        var fac = dSplit[0];
        var dept = (dSplit.length > 1) ? dSplit[1] : "";
        this.set('department_fac', fac);
        this.set('department_dept', dept);
        
        this.bind('change:department_fac', function(){
            this.set('department', this.get('department_fac') + " / " + this.get('department_dept'));
        });
        this.bind('change:department_dept', function(){
            this.set('department', this.get('department_fac') + " / " + this.get('department_dept'));
        });
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
        department_fac: "",
        department_dept: "",
        position: "",
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


/**
 * PersonRoleString Model
 */
PersonClipboard = Backbone.Model.extend({

    initialize: function(){

    },

    url: function(){
        return wgServer + wgScriptPath + '/index.php?action=api.clipboard';
    },

    defaults: function(){
        return {
            id: null,
            user_id: "",
            objs: [],
            date: ""
        }
    }

});
