BibliographyEditView = Backbone.View.extend({

    isDialog: false,
    timeout: null,
    productView: null,

    initialize: function(){
        this.model.fetch({
            error: $.proxy(function(e){
                this.$el.html("This Bibliography does not exist");
            }, this)
        });
        this.listenTo(this.model, "sync", this.render);
        this.listenTo(this.model, "change:title", function(){
            if(!this.isDialog){
                main.set('title', this.model.get('title'));
            }
        });
        this.template = _.template($('#bibliography_edit_template').html());
        
        this.allProducts = new Products();
        this.allProducts.category = 'Publication';
        this.allProducts.fetch();
        this.listenTo(this.allProducts, "sync", this.renderProductsWidget);
        $(document).mousedown(this.hidePreview);
    },
    
    saveBibliography: function(){
        this.$(".throbber").show();
        this.$("#saveBibliography").prop('disabled', true);
        this.model.save(null, {
            success: $.proxy(function(){
                this.$(".throbber").hide();
                this.$("#saveBibliography").prop('disabled', false);
                clearAllMessages();
                document.location = this.model.get('url');
            }, this),
            error: $.proxy(function(o, e){
                this.$(".throbber").hide();
                this.$("#saveBibliography").prop('disabled', false);
                clearAllMessages();
                if(e.responseText != ""){
                    addError(e.responseText, true);
                }
                else{
                    addError("There was a problem saving the Bibliography", true);
                }
            }, this)
        });
    },
    
    cancel: function(){
        document.location = this.model.get('url');
    },
    
    previewProduct: function(e){
        if(this.timeout != null){
            clearTimeout(this.timeout);
        }
        if(this.productView != null){
            this.productView.stopListening();
        }
        this.timeout = setTimeout($.proxy(function(){
            var id = $(e.currentTarget).attr('data-id');
            var product = new Product({id: id});
            this.productView = new ProductView({el: $("#preview"), model: product});
            this.productView.listenTo(product, "sync", $.proxy(function(){
                main.set('title', this.model.get('title'));
                this.productView.$el.prepend("<h1>" + product.get('title') + "</h1>");
                
                var widthBefore = $(document).width();
                var heightBefore = $(document).height();
                
                $("#editProduct").hide();
                $("#deleteProduct").hide();
                $("#preview").css('left', $(e.currentTarget).position().left + $(e.currentTarget).outerWidth() + 30 - $("#preview").width()/4);
                $("#preview").css('top', $(e.currentTarget).position().top - $("#preview").height()/2);
                $("#preview").show();
                var widthAfter = $(document).width();
                var heightAfter = $(document).height();
                
                if(widthAfter != widthBefore){
                    $("#preview").css('left', $(e.currentTarget).position().left + $(e.currentTarget).outerWidth() + 30 - $("#preview").width()/4 - (widthAfter - widthBefore + 5));
                }
                if(heightAfter != heightBefore){
                    $("#preview").css('top', $(e.currentTarget).position().top - $("#preview").height()/2 - (heightAfter - heightBefore + 5));
                }
            }, this));
        }, this), 50);
    },
    
    hidePreview: function(e){
        if(this.timeout != null){
            clearTimeout(this.timeout);
        }
        if(this.productView != null){
            this.productView.stopListening();
        }
        $("#preview").hide();
    },
    
    events: {
        "click #saveBibliography": "saveBibliography",
        "click #cancel": "cancel",
        "mouseover .sortable-list li": "previewProduct",
        "mouseout .sortable-list li": "hidePreview"
    },
    
    renderProductsWidget: function(){
        var model = this.model;
        if(headerColor != "#333333"){
            // Headers were changed, use this color
            this.$(".sortable-header").css("background", headerColor);
        }
        else{
            // Otherwise use the highlight color
            this.$(".sortable-header").css("background", highlightColor);
        }
        if(this.allProducts.length == 0){
            return;
        }
        
        var products = this.model.get('products');
        
        // Left Side (Current)
        _.each(products, $.proxy(function(id){
            var product = this.allProducts.findWhere({id: id.toString()});
            if(product != null){
                var authors = _.pluck(product.get('authors'), 'fullname').join(" ");
                this.$("#sortable1").append("<li data-id='" + product.get('id') + "'>" + product.get('title') + "<span style='display:none;'>" + authors + "</span></li>");
            }
        }, this));
        
        //Right Side (Available)
        this.allProducts.each($.proxy(function(product){
            var authors = _.pluck(product.get('authors'), 'fullname').join(" ");
            if(!_.contains(products, parseInt(product.get('id')))){
                this.$("#sortable2").append("<li data-id='" + product.get('id') + "'>" + product.get('title') + "<span style='display:none;'>" + authors + "</span></li>");
            }
        }, this));
    
        // Advanced groups
	    [{
		    name: 'advanced',
		    pull: true,
		    put: true
	    },
	    {
		    name: 'advanced',
		    pull: true,
		    put: true
	    }].forEach(function (groupOpts, i) {
		    Sortable.create(byId('sortable' + (i + 1)), {
			    sort: (i != 1),
			    group: groupOpts,
			    animation: 150,
			    onSort: function (e) {
                    if($(e.target).attr('id') == 'sortable1'){
                        var ids = new Array();
                        $("li:visible", $(e.target)).each(function(i, el){
                            ids.push(parseInt($(el).attr('data-id')));
                        });
                        model.set('products', ids);
                    }
                }
		    });
	    });
	    
	    var changeFn = function(){
	        var value = this.$(".sortable-search input").val().trim();
	        var unaccented = unaccentChars(value);
	        $("#sortable2 li").each(function(i, el){
	            if(unaccentChars($(el).text()).indexOf(unaccented) !== -1 || value == ""){
	                $(el).show();
	            }
	            else{
	                $(el).hide();
	            }
	        });
	    };
	    
	    this.$(".sortable-search input").change($.proxy(changeFn, this));
	    this.$(".sortable-search input").keyup($.proxy(changeFn, this));
    },
    
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        this.renderProductsWidget();
        return this.$el;
    }

});
