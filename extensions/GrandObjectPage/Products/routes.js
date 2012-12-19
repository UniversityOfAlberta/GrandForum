PageRouter = Backbone.Router.extend({
    routes: {
        ":category/:id": "showProduct"
    }
});

// Initiate the router
var pageRouter = new PageRouter;

pageRouter.on('route:showProduct', function (category, id) {
    product = new Product({'id': id});
});

// Start Backbone history a necessary step for bookmarkable URL's
Backbone.history.start();
