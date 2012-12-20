ProductListView = Backbone.View.extend({

    productTag: null,

    initialize: function(){
        this.model.fetch();
        this.model.bind('reset', this.render, this);
        this.template = _.template($('#product_list_template').html());
    },
    
    appendNewProduct: function(product){
        this.productTag.append(new ProductRowView({model:product}).render());
    },
    
    renderProducts: function(){
        this.productTag = this.$el.find('#productRows');
        _.each(this.model.models, function(product){
            this.appendNewProduct(product);
        }, this);
    },
    
    render: function(){
        this.$el.empty();
        this.$el.css('display', 'none');
        this.$el.html(this.template(this.model.toJSON()));
        this.renderProducts();
        this.$el.find('#listTable').dataTable({'iDisplayLength': 100,
	                                           'aaSorting': [ [0,'desc'], [1,'asc'], [4, 'asc'] ],
	                                           'aLengthMenu': [[10, 25, 100, 250, -1], [10, 25, 100, 250, 'All']]});
        this.$el.css('display', 'block');
        return this.el;
    }

});

productRowTemplate = _.template($('#product_row_template').html());

ProductRowView = Backbone.View.extend({
    
    tagName: 'tr',
    
    initialize: function(){
        this.template = productRowTemplate;
    },
    
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        return this.el;
    }
    
});
