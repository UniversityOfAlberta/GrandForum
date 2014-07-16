AddProductView = Backbone.View.extend({

    initialize: function(){
        this.template = _.template($("#add_product_template").html());
        this.$el.hide();
    },
    
    createProduct: function(){
        this.model.save();
    },
    
    events: {
        "click #createProduct": "createProduct"
    },
    
    render: function(){
        if(this.model != undefined){
            this.$el.html(this.template(this.model.toJSON()));
            this.$el.slideDown();
        }
        else{
            this.$el.slideUp();
        }
        return this.$el;
    }

});
