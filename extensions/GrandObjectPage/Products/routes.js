PageRouter = Backbone.Router.extend({
    routes: {
        ":category/:id": "showProduct"
    }
});

// Initiate the router
var pageRouter = new PageRouter;

pageRouter.on('route:showProduct', function(category, id) {
    var product = new Product({'id': id, 'category': category});
});

// Start Backbone history a necessary step for bookmarkable URL's
Backbone.history.start();
