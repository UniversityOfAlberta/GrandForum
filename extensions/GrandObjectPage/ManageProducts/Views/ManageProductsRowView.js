ManageProductsViewRow = Backbone.View.extend({
    
    tagName: 'tr',
    parent: null,
    row: null,
    duplicating: false,
    template: _.template($('#manage_products_row_template').html()),
    
    initialize: function(options){
        this.parent = options.parent;
        this.listenTo(this.model, "change", this.render);
        this.listenTo(this.model, "change:access_id", this.setDirty, true);
        this.listenTo(this.model, "change:exclude", this.setDirty, true);
    },
    
    setDirty: function(trigger){
        this.model.dirty = true;
        if(trigger){
            this.model.trigger("dirty");
        }
    },
    
    editProduct: function(){
        var view = new ProductEditView({el: this.parent.editDialog, model: this.model, isDialog: true});
        this.parent.editDialog.view = view;
        this.parent.editDialog.dialog({
            height: $(window).height()*0.75, 
            width: 800,
            title: "Edit " + productsTerm
        });
        this.parent.editDialog.dialog('open');
    },
    
    deleteProduct: function(){
        this.parent.deleteDialog.model = this.model;
        this.parent.deleteDialog.dialog('open');
    },
    
    duplicateProduct: function(){
        if(!this.duplicating){
            // Only duplicate if there isn't already a pending one happening
            this.$(".copy-icon").css('background', 'none');
            this.$(".copy-icon .throbber").show();
            this.duplicating = true;
            var product = new Product(this.model.toJSON());
            product.set('id', null);
            product.save(null, {
                success: function(){
                    clearSuccess();
                    clearError();
                    addSuccess('The ' + product.get('category') + ' <i>' + product.get('title') + '</i> was duplicated');
                    this.parent.products.add(product);
                    _.each(this.parent.subViews, function(val, key){
                        if(val.model.get('id') == product.get('id')){
                            val.editProduct();
                        }
                    });
                    this.duplicating = false;
                    this.$(".copy-icon").css('background', '');
                    this.$(".copy-icon .throbber").hide();
                }.bind(this),
                error: function(){
                    clearSuccess();
                    clearError();
                    addError('There was a problem duplicating the ' + product.get('category') + ' <i>' + product.get('title') + '</i>');
                    this.duplicating = false;
                    this.$(".copy-icon").css('background', '');
                    this.$(".copy-icon .throbber").hide();
                }.bind(this)
            });
        }
    },
    
    events: {
        "change input.popupBlockSearch": "filterSearch",
        "keyup input.popupBlockSearch": "filterSearch",
        "click .edit-icon": "editProduct",
        "click .copy-icon": "duplicateProduct",
        "click .delete-icon": "deleteProduct"
    },
    
    render: function(){
        var classes = new Array();
        this.$("td").each(function(i, val){
            classes.push($(val).attr("class"));
        });
        var isMine = {isMine: false};
        if(_.contains(_.pluck(this.model.get('authors'), 'id'), me.get('id')) ||
           _.intersection(_.pluck(this.model.get('authors'), 'id'), students).length > 0){
            isMine.isMine = true;
        }
        
        //Sanity Check: If there is ONLY title and year (no data), set incomplete == true;
        var incomplete = {
                          incomplete: true,
                          peerReviewedMissing: false,
                          impactFactorMissing: false
                         };
        
        // bind rebinds this to val to this.model.get('cat') instead of 'type'
        if(productStructure.categories[this.model.get('category')].
           types[this.model.getType()].data.length == 0){
            incomplete.incomplete = false;
        }
        else{
            _.each(productStructure.categories[this.model.get('category')].
                   types[this.model.getType()].data, function(val, key){
                        if(this.model.get('data')[key] != undefined && String(this.model.get('data')[key]).trim() != ""){
                            incomplete.incomplete = false;
                        }
                }.bind(this)
            );
        }

        if(this.model.get('category') == "Publication" && 
           (typeof(this.model.get('data')['peer_reviewed']) == 'undefined' ||
            this.model.get('data')['peer_reviewed'] === null ||
            this.model.get('data')['peer_reviewed'].trim() == "")){
            incomplete.peerReviewedMissing = true;
        }
        
        if(this.model.get('category') == "Publication" &&
           productStructure.categories[this.model.get('category')].types[this.model.getType()].data['impact_factor'] != undefined &&
           (typeof(this.model.get('data')['impact_factor']) == 'undefined' ||
            this.model.get('data')['impact_factor'] === null ||
            this.model.get('data')['impact_factor'].trim() == "") && 
           (typeof(this.model.get('data')['impact_factor_override']) == 'undefined' ||
            this.model.get('data')['impact_factor_override'] === null ||
            this.model.get('data')['impact_factor_override'].trim() == "") &&
            productStructure.categories[this.model.get('category')].types[this.model.getType()].data['snip'] != undefined &&
           (typeof(this.model.get('data')['snip']) == 'undefined' ||
            this.model.get('data')['snip'] === null ||
            this.model.get('data')['snip'].trim() == "")){
            incomplete.impactFactorMissing = true;
        }
        
        this.el.innerHTML = this.template(_.extend(this.model.toJSON(), isMine, incomplete));
        if(this.parent.table != null){
            // Need this so that the search functionality is updated
            var data = new Array();
            this.$("td").each(function(i, val){
                data.push($(val).htmlClean().html());
            });
            if(this.row != null){
                this.row.data(data);
            }
        
        }
        if(classes.length > 0){
            this.$("td").each(function(i, val){
                $(val).addClass(classes[i]);
            });
        }
        if(this.parent.table != null){
            this.parent.table.draw();
        }
        return this.$el;
    }

});
