/**
 * Product Model
 */
Product = Backbone.Model.extend({
    
    initialize: function(){
        this.authors = new ProductAuthors();
        this.authors.url = this.urlRoot + '/' + this.get('id') + '/authors';
        
        this.projects = new ProductAuthors();
        this.projects.url = this.urlRoot + '/' + this.get('id') + '/projects';
        
        this.duplicates = new ProductDuplicates();
              
        this.on("change:category", function(){
            var type = this.get('type').split(":")[0];
            if(this.get('category') != "" && productStructure.categories[this.get('category')].types[type] == undefined){
                this.set("type", ""); // Clear type
            }
        });
        
        this.on("change:type", function(){
            var status = this.get('status');
            if(productStructure.categories[this.get('category')] !== undefined &&
               productStructure.categories[this.get('category')].types[this.get('type')] != undefined &&
               !_.contains(productStructure.categories[this.get('category')].types[this.get('type')].status, status)){
                this.set('status', _.first(productStructure.categories[this.get('category')].types[this.get('type')].status));
            }
        });
    },

    getAuthors: function(){
        this.authors.fetch();
        return this.authors;
    },
    
    getProjects: function(){
        this.projects.fetch();
        return this.projects;
    },
    
    getDuplicates: function(){
        this.duplicates.category = this.get('category');
        this.duplicates.title = this.get('title');
        this.duplicates.id = this.get('id');
        this.duplicates.fetch();
        return this.duplicates;
    },
    
    getLink: function(){
        return new Link({id: this.get('id'),
                         text: this.get('title'),
                         url: this.get('url'),
                         target: ''});
                               
    },
    
    getPossibleCategories: function(){
        return productStructure.categories;
    },
    
    getPossibleTypes: function(){
        if(this.get('category') == ""){
            return new Array();
        }
        return _.keys(productStructure.categories[this.get('category')].types).sort();
    },
    
    getPossibleMiscTypes: function(){
        return productStructure.categories[this.get('category')].misc;
    },
    
    getPossibleFields: function(){
        var type = this.get('type').split(":")[0];
        if(type == "" || this.get('category') == ""){
            return new Array();
        }
        if(productStructure.categories[this.get('category')].types[type] == undefined){
            return _.first(_.values(productStructure.categories[this.get('category')].types)).data;
        }
        return productStructure.categories[this.get('category')].types[type].data;
    },
    
    getPossibleStatus: function(){
        var type = this.get('type').split(":")[0];
        if(type == "" || this.get('category') == ""){
            return new Array();
        }
        if(productStructure.categories[this.get('category')].types[type] == undefined){
            return _.first(_.values(productStructure.categories[this.get('category')].types)).status;
        }
        return productStructure.categories[this.get('category')].types[type].status;
    },

    urlRoot: 'index.php?action=api.product',
    
    defaults: {
        id : null,
        title: "",
        category: "",
        type: "",
        description: "",
        date: Date.format(new Date(), 'yyyy-MM-dd'),
        url: "",
        status: "",
        data: {},
        authors: new Array(),
        projects: new Array(),
        lastModified: "",
        deleted: "",
        access_id: 0,
        created_by: 0,
        access: "Forum"
    },
});

/**
 * Products Collection
 */
Products = Backbone.Collection.extend({
    
    model: Product,
    
    project: 'all',
    
    category: 'all',
    
    grand: 'both',
    
    fetch: function(options) {
        if(_.isFunction(this.url)){
            this.temp = [];
            var self = this;

            this.fetchChunk(0, 1000); // Fetch 1000 at a time
        }
        else{
            return Backbone.Collection.prototype.fetch.call(this, options);
        }
    },
    
    fetchChunk: function(start, count){
        var url = this.url() + '/' + start + '/' + count;
        var self = this;
        $.get(url, function(data) {
            self.temp = self.temp.concat(data);
            if(_.size(data) == count){
                // There's probably more, so keep calling
                self.fetchChunk(start + count, count);
            }
            else{
                // Done fetching
                self.reset(self.temp);
                self.trigger('sync');
            }
        });
    },
    
    url: function(){
        var url = 'index.php?action=api.product/' + this.project + '/' + this.category + '/' + this.grand;
        return url;
    }
});

/**
 * ProductDuplicates Collection
 */
ProductDuplicates = Backbone.Collection.extend({
    
    model: Product,
    
    xhrs: new Array(),
    
    category: '',
    
    title: '',
    
    id: '',
    
    fetch: function(options){
        var xhr = Backbone.Collection.prototype.fetch.call(this, options);
        this.xhrs.push(xhr);
        return xhr;
    },
    
    ready: function(){
        return $.when.apply(null, this.xhrs);
    },
    
    url: function(){
        var url = 'index.php?action=api.productDuplicates/' + this.category + '/' + this.title.replace(/[^a-zA-Z0-9-_]/g, '') + '/' + this.id;
        return url;
    },
    
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
