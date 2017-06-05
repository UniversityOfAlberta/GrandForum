BibliographyView = Backbone.View.extend({

    initialize: function(){
        Backbone.Subviews.add(this);
        this.model.fetch({
            error: $.proxy(function(e){
                this.$el.html("This Bibliography does not exist");
            }, this)
        });
        this.model.bind('change', this.render, this);
        this.template = _.template($('#bibliography_template').html());
    },

    subviewCreators: {
        "listComments": function(){
            console.log("THREAD ID: " + this.model.get('thread_id'));
            return new ThreadView({model: new Thread({id: this.model.get('thread_id')})});
        }
    },
    
    editBibliography: function(){
        document.location = this.model.get('url') + "/edit";
    },
    
    events: {
        "click #editBibliography": "editBibliography"
    },
    
    renderProducts: function(){
        var xhrs = new Array();
        var products = new Array();
        var citations = new Array();
        _.each(this.model.get('products'), function(prod){
            var product = new Product({id: prod});
            products.push(product);
            xhrs.push(product.fetch());
        });
        $.when.apply(null, xhrs).done($.proxy(function(){
            var xhrs2 = new Array();
            _.each(products, function(product){
                xhrs2.push(product.getCitation());
            });
            $.when.apply(null, xhrs2).done($.proxy(function(){
                _.each(products, $.proxy(function(product){
                    this.$('#products ol').append("<li>" + product.get('citation') + "<br /><div style='width:100%;text-align:right;'>&nbsp;" + product.get('tags').join(", ") + "</div></li>");
                }, this));
                $(".pdfnodisplay").remove();
            }, this));
        }, this));
    },
    
    render: function(){
        main.set('title', this.model.get('title'));
        this.$el.empty();
        this.$el.html(this.template(this.model.toJSON()));
        this.renderProducts();
        return this.$el;
    }

});
