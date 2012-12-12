Product = Backbone.RelationalModel.extend({
    initialize: function(){
        //this.get('authors').url = this.urlRoot + '/' + this.get('id') + '/authors';
    //    this.bind('change:id', function(){
    //        this.get('authors').url = this.urlRoot + '/' + this.get('id') + '/authors';
    //    });
    },

   relations: [{
        type: Backbone.HasMany,
        key: 'authors',
        relatedModel: 'ProductAuthor',
        reverseRelation: {
            key: 'productId',
            includeInJSON: 'id',
        },
        collectionType: 'ProductAuthors',

    }],

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


ProductAuthor = Backbone.RelationalModel.extend({

    urlRoot: 'index.php?action=api.person_product/product_id',
    idAttribute: 'productId',
    
    defaults: {
        productId: null,
        personId: null
    }
});

/*AuthorProducts = Backbone.RelationalModel.extend({
    
    initialize: function(){

    },
    
    relations: [{
        type: Backbone.HasOne,
        key: 'person',
        relatedModel: 'Person'
    },
    {
        type: Backbone.HasOne,
        key: 'product',
        relatedModel: 'Product'
    }],

    urlRoot: function(){
        return 'index.php?action=api.person/' + this.personId + '/projects'
    },
    
    defaults: {
        projectId: "",
        personId: "",
        startDate: "",
        endDate: ""
    }
});*/


Products = Backbone.Collection.extend({
    model: Product,
    
    url: 'index.php?action=api.product'
});

ProductAuthors = Backbone.Collection.extend({
    model: ProductAuthor,
});
