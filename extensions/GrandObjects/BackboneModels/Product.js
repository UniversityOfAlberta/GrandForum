/**
 * Product Model
 */
Product = Backbone.Model.extend({
    initialize: function(){
        this.authors = new ProductAuthors();
        this.authors.url = this.urlRoot + '/' + this.get('id') + '/authors';
        
        this.projects = new ProductAuthors();
        this.projects.url = this.urlRoot + '/' + this.get('id') + '/projects';
    },

    getAuthors: function(){
        this.authors.fetch();
        return this.authors;
    },
    
    getProjects: function(){
        this.projects.fetch();
        return this.projects;
    },

    urlRoot: 'index.php?action=api.product',
    
    defaults: {
        id : null,
        title: "",
        category: "",
        type: "",
        description: "",
        date: "",
        status: "",
        data: new Array(),
        lastModified: "",
        deleted: ""
    },
});

/**
 * Products Collection
 */
Products = Backbone.Collection.extend({
    model: Product,
    
    url: 'index.php?action=api.product'
});

/**
 * ProductAuthor RelationModel
 */
ProductAuthor = RelationModel.extend({
    initialize: function(){
    
    },

    urlRoot: function(){
        return 'index.php?action=api.product/' + this.get('productId') + '/authors'
    },
    
    idAttribute: 'personId',
    
    getOwner: function(){
        product = new Product({id: this.get('productId')});
        person.fetch();
        return person;
    },
    
    getTarget: function(){
        person = new Person({id: this.get('personId')});
        person.fetch();
        return person;
    },
    
    defaults: {
        productId: null,
        personId: null,
        startDate: "",
        endDate: "",
    }
});

/**
 * ProductAuthors RangeCollection
 */
ProductAuthors = RangeCollection.extend({
    model: ProductAuthor,
    
    newModel: function(){
        return new People();
    },
});

/**
 * ProductProject RelationModel
 */
ProductProject = RelationModel.extend({
    initialize: function(){
    
    },

    urlRoot: function(){
        return 'index.php?action=api.product/' + this.get('productId') + '/projects'
    },
    
    idAttribute: 'projectId',
    
    getOwner: function(){
        product = new Product({id: this.get('productId')});
        person.fetch();
        return person;
    },
    
    getTarget: function(){
        person = new Project({id: this.get('projectId')});
        person.fetch();
        return person;
    },
    
    defaults: {
        productId: null,
        projectId: null,
        startDate: "",
        endDate: "",
    }
});

/**
 * ProductProjects RangeCollection
 */
ProductProjects = RangeCollection.extend({
    model: ProductProject,
    
    newModel: function(){
        return new Projects();
    },
});
