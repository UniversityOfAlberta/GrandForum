DuplicatesDialogView = Backbone.View.extend({

    products: null,

    attributes: {
        title: "Duplicates Detected"
    },

    initialize: function(products){
        this.products = products;
        this.template = _.template($("#duplicates_dialog_template").html());
        this.$el.dialog({
            autoOpen: false,
            modal: true,
            resizable: false,
            draggable: false,
            show: 'fade',
            width: "800px",
            beforeClose: $.proxy(function(){
                $("html").css("overflow", "auto");
            }, this),
            buttons: {
                "Not Duplicates": $.proxy(this.notDuplicates, this),
                "Delete Selected": $.proxy(this.deleteSelectedProducts, this)
            }
        });
        setInterval($.proxy(function(){
            var innerHeight = parseInt(this.$el.height());
            var outerHeight = parseInt(this.$el.parent().height());
            var outerTop = parseInt(this.$el.parent().css('top'));
            this.$el.css("maxHeight", $(window).height() - (outerTop + (outerHeight - innerHeight)) - 50);
        }, this), 100);
    },
    
    notDuplicates: function(){
        var firstProduct = _.first(this.model);
        if(firstProduct == undefined){
            this.next();
            return;
        }
        var duplicates = firstProduct.duplicates;
        firstProduct.save(null, {
            success: $.proxy(function(){
                duplicates.each($.proxy(function(duplicate){
                    var url = wgServer + wgScriptPath + "/index.php?action=ignoreDuplicates&handler=my" + firstProduct.get('category');
                    $.post(url, {
                        id1: firstProduct.get('id'), 
                        id2: duplicate.get('id')
                    });
                    // Remove future duplicates so we don't see them again
                    duplicate.duplicates.reset(duplicate.duplicates.filter(function(dup){
                        return (firstProduct.get('id') != dup.get('id'));
                    }));
                    if(duplicates != undefined){
                        duplicates.each($.proxy(function(dupe){
                            if(dupe.get('id') != duplicate.get('id')){
                                $.post(url, {
                                    id1: dupe.get('id'), 
                                    id2: duplicate.get('id')
                                });
                                // Remove future duplicates so we don't see them again
                                dupe.duplicates.reset(dupe.duplicates.filter(function(dup){
                                    return (duplicate.get('id') != dup.get('id'));
                                }));
                                duplicate.duplicates.reset(duplicate.duplicates.filter(function(dup){
                                    return (dupe.get('id') != dup.get('id'));
                                }));
                            }
                        }, this));
                    }
                }, this));
                firstProduct.dirty = false;
                firstProduct.trigger("dirty");

                if(this.products.indexOf(firstProduct) == -1){
                    this.products.add(firstProduct);
                }
                this.next();
            }, this),
            error: $.proxy(function(){
                this.next();
            }, this)
        });
    },
    
    deleteSelectedProducts: function(){
        var firstProduct = _.first(this.model);
        if(!this.$("input[value=" + firstProduct.get('id') + "]").is(":checked")){
            firstProduct.save({
                success: function(){
                    firstProduct.dirty = false;
                    firstProduct.trigger("dirty");
                },
                error: function(){
                    
                }
            });
        }
        this.$("input[type=checkbox]:checked").each($.proxy(function(i, box){
            var id = $(box).val();
            var duplicate = null;
            if(firstProduct.get('id') == id){
                duplicate = firstProduct;
            }
            else{
                duplicate = firstProduct.duplicates.findWhere({id: id});
            }
            this.products.remove(id);
            duplicate.destroy({
                success: function(){
                    
                },
                error: function(){
                
                }
            });
        }, this));
        this.next();
    },
    
    next: function(){
        // Changes the dialog to the next duplicate product
        var firstProduct = _.first(this.model);
        this.model = _.without(this.model, firstProduct);
        if(this.model.length > 0){
            this.render();
        }
        else{
            this.close();
        }
    },
    
    open: function(){
        this.render();
        this.$el.dialog('open');
        $("html").css("overflow", "hidden");
    },
    
    close: function(){
        this.$el.dialog('close');
    },
    
    render: function(){
        var firstProduct = _.first(this.model);
        if(firstProduct.duplicates.length == 0){
            // Duplicates were previously dealt with, go to next
            firstProduct.save(null, {
                success: function(){
                    firstProduct.dirty = false;
                    firstProduct.trigger("dirty");
                },
                error: function(){
                
                }
            });
            this.next();
            return this.$el;
        }
        this.$el.html(this.template(_.extend(firstProduct.toJSON(), {duplicates: firstProduct.duplicates})));
        this.$("div#duplicatesRemaining").empty();
        if(this.model.length - 1 > 0){
            // Show how many duplicates are remaining, but only if there are more than 1
            this.$("div#duplicatesRemaining").html((this.model.length - 1) + " more duplicates remaining");
        }
        return this.$el;
    }

});
