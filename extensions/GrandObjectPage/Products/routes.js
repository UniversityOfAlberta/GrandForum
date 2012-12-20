PageRouter = Backbone.Router.extend({
    
    initialize: function(){
        this.bind('all', function(event){
            $("#currentView").html("<div id='currentViewSpinner'></div>");
            spin = spinner("currentViewSpinner", 40, 75, 12, 10, '#888');
        });
    },

    routes: {
        ":category": "showProducts",
        ":category/new": "newProduct",
        ":category/:id": "showProduct",
        ":category/:id/edit": "editProduct"
    }
});

// Initiate the router
var pageRouter = new PageRouter;

pageRouter.on('route:showProducts', function(category){
    // Get All Products
    products = new Products();
    products.category = category;
    products.grand = 'grand';
    productListView = new ProductListView({el: $("#currentView"), model: products});
});

pageRouter.on('route:newProduct', function(category){
    // Create New Product
    products = new Product();
});

pageRouter.on('route:showProduct', function (category, id) {
    // Get A single product
    product = new Product({'id': id});
});

pageRouter.on('route:editProduct', function (category, id) {
    // Get A single product
    product = new Product({'id': id});
});

// Start Backbone history a necessary step for bookmarkable URL's
Backbone.history.start();
