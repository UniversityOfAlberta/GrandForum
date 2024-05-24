/**
 * Product Model
 */
Product = Backbone.Model.extend({
    
    initialize: function(){
        this.authors = new ProductAuthors();
        this.authors.url = this.urlRoot + '/' + this.get('id') + '/authors';
        
        this.duplicates = new ProductDuplicates();
              
        this.on("change:category", function(){
            var type = this.getType();
            if(this.get('category') != "" && productStructure.categories[this.get('category')].types[type] == undefined){
                this.set("type", ""); // Clear type
            }
        });
        
        this.on("change:type", function(){
            var status = this.get('status');
            if(productStructure.categories[this.get('category')] !== undefined &&
               productStructure.categories[this.get('category')].types[this.getType()] != undefined &&
               !_.contains(productStructure.categories[this.get('category')].types[this.getType()].status, status)){
                this.set('status', _.first(productStructure.categories[this.get('category')].types[this.getType()].status));
            }
        });
        
        this.on("change:status", function(){
            var status = this.get('status');
            if(status == "Published"){
                this.set('access', 'Public');
            }
            else{
                this.set('access', 'Forum');
            }
        });
    },
    
    getType: function(){
        return this.get('type').split(":")[0];
    },

    getAuthors: function(){
        this.authors.fetch();
        return this.authors;
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
        var type = this.getType();
        if(type == "" || this.get('category') == ""){
            return new Array();
        }
        if(productStructure.categories[this.get('category')].types[type] == undefined){
            return _.first(_.values(productStructure.categories[this.get('category')].types)).data;
        }
        return productStructure.categories[this.get('category')].types[type].data;
    },
    
    getPossibleStatus: function(){
        var type = this.getType();
        if(type == "" || this.get('category') == ""){
            return new Array();
        }
        if(productStructure.categories[this.get('category')].types[type] == undefined){
            return _.first(_.values(productStructure.categories[this.get('category')].types)).status;
        }
        return productStructure.categories[this.get('category')].types[type].status;
    },
    
    getAcceptanceDateLabel: function(){
        var type = this.getType();
        if(type == "" || this.get('category') == ""){
            return new Array();
        }
        if(productStructure.categories[this.get('category')].types[type] == undefined){
            return _.first(_.values(productStructure.categories[this.get('category')].types)).acceptance_date_label;
        }
        return productStructure.categories[this.get('category')].types[type].acceptance_date_label;
    },
    
    getDateLabel: function(){
        var type = this.getType();
        if(type == "" || this.get('category') == ""){
            return new Array();
        }
        if(productStructure.categories[this.get('category')].types[type] == undefined){
            return _.first(_.values(productStructure.categories[this.get('category')].types)).date_label;
        }
        return productStructure.categories[this.get('category')].types[type].date_label;
    },
    
    getAuthorsLabel: function(){
        var type = this.getType();
        if(this.get('category') == ""){
            return "Author";
        }
        var label = "";
        if(productStructure.categories[this.get('category')].types[type] == undefined){
            label = _.first(_.values(productStructure.categories[this.get('category')].types)).authors_label;
        }
        else{
            label = productStructure.categories[this.get('category')].types[type].authors_label;
        }
        if(label == undefined){
            return "Author";
        }
        return label;
    },
    
    isSingleAuthor: function(){
        var type = this.getType();
        if(this.get('category') == ""){
            return false;
        }
        var single = "";
        if(productStructure.categories[this.get('category')].types[type] == undefined){
            single = _.first(_.values(productStructure.categories[this.get('category')].types)).authors_single;
        }
        else{
            single = productStructure.categories[this.get('category')].types[type].authors_single;
        }
        if(single == undefined){
            return false;
        }
        return single;
    },
    
    hasContributors: function(){
        var type = this.getType();
        if(this.get('category') == ""){
            return false;
        }
        var contrib = false;
        if(productStructure.categories[this.get('category')].types[type] == undefined){
            contrib = (_.first(_.values(productStructure.categories[this.get('category')].types)).contributors_label != undefined);
        }
        else{
            contrib = (productStructure.categories[this.get('category')].types[type].contributors_label != undefined);
        }
        return contrib;
    },

    getContributorsLabel: function(){
        var type = this.getType();
        if(this.get('category') == ""){
            return "Contibutor";
        }
        var label = "";
        if(productStructure.categories[this.get('category')].types[type] == undefined){
            label = _.first(_.values(productStructure.categories[this.get('category')].types)).contributors_label;
        }
        else{
            label = productStructure.categories[this.get('category')].types[type].contributors_label;
        }
        if(label == undefined){
            return "Contributor";
        }
        return label;
    },

    urlRoot: 'index.php?action=api.product',
    
    defaults: function() {
        return {
            id : null,
            title: "",
            category: "",
            type: "",
            description: "",
            date: Date.format(new Date(), 'yyyy-MM-dd'),
            acceptance_date: Date.format(new Date(), 'yyyy-MM-dd'),
            url: "",
            status: "",
            data: {},
            authors: new Array(),
            contributors: new Array(),
            lastModified: "",
            deleted: "",
            access_id: 0,
            created_by: 0,
            reported: "",
            access: "Forum",
            exclude: false,
            canDelete: true,
        };
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
            this.fetchChunk(0, 1000); // Fetch 1000 at a time
        }
        else if(_.isArray(this.url)){
            this.temp = [];
            this.fetchMultiple(0, 100); // Fetch 100 at a time
        }
        else{
            return Backbone.Collection.prototype.fetch.call(this, options);
        }
    },
    
    fetchMultiple: function(start, count){
        var rest = _.first(_.rest(this.url, start+1), count);
        var url = this.url[0] + '-1,' + rest.join(',');
        var self = this;
        $.get(url, function(data) {
            self.temp = self.temp.concat(data);
            if(_.size(data) == count){
                // There's probably more, so keep calling
                self.fetchMultiple(start + count, count);
            }
            else{
                // Done fetching
                self.reset(self.temp);
                self.trigger('sync');
            }
        });
    },
    
    fetchChunk: function(start, count){
        var url = this.url() + '/' + start + '/' + count;
        var self = this;
        $.get(url, function(data) {
            if(_.size(data) == count){
                // There's probably more, so keep calling
                self.add(data, {silent: true});
                self.trigger('partialSync', start, count);
                self.fetchChunk(start + count, count);
            }
            else{
                // Done fetching
                self.add(data, {silent: true});
                self.trigger('sync', start, count);
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
 * ProductHistory Model
 */
ProductHistory = Backbone.Model.extend({
    
    initialize: function(){
    
    },
    
    urlRoot: 'index.php?action=api.productHistories',
    
    defaults: {
        id: null,
        user_id: null,
        type: "",
        year: "",
        value: ""
    }
    
});

/**
 * ProductHistories Collection
 */
ProductHistories = Backbone.Collection.extend({
    
    model: ProductHistory,
    
    personId: null,
    
    url: function(){
        return 'index.php?action=api.productHistories/person/' + this.personId;
    },
    
});
