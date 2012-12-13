Product = Backbone.Model.extend({
    initialize: function(){
        this.authors = new ProductAuthors();
        this.authors.url = this.urlRoot + '/' + this.get('id') + '/authors';
    },

    getAuthors: function(){
        this.authors.fetch();
        return this.authors;
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

ProductAuthor = RelationModel.extend({

    urlRoot: function(){
        return 'index.php?action=api.product/' + this.productId + '/authors'
    },
    
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

Products = Backbone.Collection.extend({
    model: Product,
    
    url: 'index.php?action=api.product'
});

ProductAuthors = RangeCollection.extend({
    model: ProductAuthor,
    
    newModel: function(){
        return new People();
    },
});
