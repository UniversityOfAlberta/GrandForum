BibliographyEditView = Backbone.View.extend({

    isDialog: false,
    timeout: null,
    productView: null,
    spinner: null,
    allPeople: null,
    bibtexDialog: null,

    initialize: function(){
        this.model.fetch({
            error: function(e){
                this.$el.html("This Bibliography does not exist");
            }.bind(this)
        });
        this.listenTo(this.model, "sync", this.render);
        this.listenTo(this.model, "change:title", function(){
            if(!this.isDialog){
                main.set('title', this.model.get('title'));
            }
        });
        this.template = _.template($('#bibliography_edit_template').html());
        
        this.allProducts = new Products();
        this.allProducts.category = _.first(_.keys(productStructure.categories));
        this.allProducts.fetch();
        
        this.allPeople = new People();
        this.allPeople.fetch();
        
        this.listenTo(this.allProducts, "sync", this.renderProductsWidget);
        this.listenTo(this.allProducts, "reset", this.renderProductsWidget);
        this.listenTo(this.allPeople, "sync", this.renderEditorsWidget);
        $(document).mousedown(this.hidePreview);
    },
    
    saveBibliography: function(){
        if (this.model.get("title").trim() == '') {
            clearWarning();
            addWarning('Title must not be empty', true);
            return;
        }
        if(this.model.get('products').length == 0){
            clearWarning();
            addWarning('At least one publication must be added', true);
            return;
        }
        this.$(".throbber").show();
        this.$("#saveBibliography").prop('disabled', true);
        this.model.save(null, {
            success: function(){
                this.$(".throbber").hide();
                this.$("#saveBibliography").prop('disabled', false);
                clearAllMessages();
                document.location = this.model.get('url');
            }.bind(this),
            error: function(o, e){
                this.$(".throbber").hide();
                this.$("#saveBibliography").prop('disabled', false);
                clearAllMessages();
                if(e.responseText != ""){
                    addError(e.responseText, true);
                }
                else{
                    addError("There was a problem saving the Bibliography", true);
                }
            }.bind(this)
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
        this.timeout = setTimeout(function(){
            var id = $(e.currentTarget).attr('data-id');
            var product = new Product({id: id});
            this.productView = new ProductView({el: $("#preview"), model: product});
            this.productView.listenTo(product, "sync", function(){
                // Reset to original title (not the Product's)
                main.set('title', this.model.get('title')); 
                this.productView.$el.prepend("<h1>" + product.get('title') + "</h1>");
                
                var widthBefore = $(document).width();
                var heightBefore = $(document).height();
                
                $("#editProduct").hide();
                $("#deleteProduct").hide();
                $("#preview").fadeIn(100);
                $("#preview").css('left', $(e.currentTarget).position().left + $(e.currentTarget).outerWidth() + 30 - $("#preview").width()/4);
                $("#preview").css('top', $(e.currentTarget).position().top - $("#preview").height()/2);
                
                var widthAfter = $(document).width();
                var heightAfter = $(document).height();
                
                if(widthAfter != widthBefore){
                    $("#preview").css('left', $(e.currentTarget).position().left + $(e.currentTarget).outerWidth() + 30 - $("#preview").width()/4 - (widthAfter - widthBefore + 5));
                }
                if(heightAfter != heightBefore){
                    $("#preview").css('top', $(e.currentTarget).position().top - $("#preview").height()/2 - (heightAfter - heightBefore + 5));
                }
            }.bind(this));
        }.bind(this), 50);
    },
    
    renderEditors: function(){
        if(this.allPeople != null && this.allPeople.length > 0){
            this.renderEditorsWidget();
        }
        else{
            this.allPeople = new People();
            this.allPeople.fetch();
            this.allPeople.bind('sync', function(){
                if(this.allPeople.length > 0){
                    this.renderEditorsWidget();
                }
            }, this);
        }
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
        "mouseover #products .sortable-list li": "previewProduct",
        "mouseout #products .sortable-list li": "hidePreview",
        "click #bibImportBibTeX": "importBibTeX"
    },
    
    renderEditorsWidget: function(){
        var model = this.model;
        this.$("#editors .sortable-header").css("background", hyperlinkColor);

        if(this.allPeople.length == 0){
            return;
        }

        var editors = this.model.get('editors');
        this.$("#editors .sortable-widget").show();
        
        // Left Side (Current)
        _.each(editors, function(e){
            var editor = this.allPeople.findWhere({id: e.id.toString()});
            if(editor != null){
                this.$("#editors #sortable1").append("<li data-id='" + editor.get('id') + "'>" + editor.get('fullName') + "</li>");
            }
        }.bind(this));
        
        //Right Side (Available)
        this.allPeople.each(function(editor){
            if(!_.contains(_.pluck(editors, 'id'), editor.get('id'))){
                this.$("#editors #sortable2").append("<li data-id='" + editor.get('id') + "'>" + editor.get('fullName') + "</li>");
            }
        }.bind(this));
    
        // Advanced groups
	    [{
		    name: 'editors',
		    pull: true,
		    put: true
	    },
	    {
		    name: 'editors',
		    pull: true,
		    put: true
	    }].forEach(function (groupOpts, i) {
		    $("#editors #sortable" + (i + 1))[0].Sortable = Sortable.create($("#editors #sortable" + (i + 1))[0], {
			    sort: (i != 1),
			    group: groupOpts,
			    animation: 150,
			    onSort: function (e) {
                    if($(e.target).attr('id') == 'sortable1'){
                        var ids = new Array();
                        $("li:visible", $(e.target)).each(function(i, el){
                            ids.push(parseInt($(el).attr('data-id')));
                        });
                        model.set('editors', ids);
                    }
                }
		    });
	    });
	    
	    var changeFn = function(){
	        var value = this.$("#editors .sortable-search input").val().trim();
	        var lower = value.toLowerCase();
	        var showElements = new Array();
	        var hideElements = new Array();
	        $("#editors #sortable2 li").each(function(i, el){
	            if($(el).text().toLowerCase().indexOf(lower) !== -1 || value == ""){
	                showElements.push(el);
	            }
	            else{
	                hideElements.push(el);
	            }
	        });
	        $(showElements).show();
	        $(hideElements).hide();
	    };
	    
	    this.$("#editors .sortable-search input").change(changeFn.bind(this));
	    this.$("#editors .sortable-search input").keyup(changeFn.bind(this));
    },
    
    renderProductsWidget: function(){
        var model = this.model;
        this.$("#products .sortable-header").css("background", hyperlinkColor);
        
        if(this.allProducts.length == 0){
            this.spinner = spinner("products", 20, 40, 10, 6, '#888');
            return;
        }
        if(this.spinner != null){
            this.spinner();
        }
        this.allProducts = new Products(this.allProducts.where({access: "Public"}));
        var products = this.model.get('products');
        this.$("#products .sortable-widget").show();
        
        // Left Side (Current)
        this.$("#products #sortable1").empty();
        _.each(products, function(p){
            if (!_.isObject(p)) {
                var product = this.allProducts.findWhere({id: p});
            } else {
                var product = this.allProducts.findWhere({id: p.id.toString()});
            }
            if(product != null){
                var authors = _.pluck(product.get('authors'), 'fullname').join(" ");
                this.$("#products #sortable1").append("<li data-id='" + product.get('id') + "'>" + product.get('title') + "<span style='display:none;'>" + authors + "</span></li>");
            }
        }.bind(this));
        
        //Right Side (Available)
        this.$("#products #sortable2").empty();
        this.allProducts.each(function(product){
            var authors = _.pluck(product.get('authors'), 'fullname').join(" ");
            if ((!_.contains(_.pluck(products, 'id'), product.get('id'))) && (!_.contains(products, product.get('id').toString()))) {
                this.$("#products #sortable2").append("<li data-id='" + product.get('id') + "'>" + product.get('title') + "<span style='display:none;'>" + authors + "</span></li>");
            }
        }.bind(this));
    
        // Advanced groups
	    [{
		    name: 'products',
		    pull: true,
		    put: true
	    },
	    {
		    name: 'products',
		    pull: true,
		    put: true
	    }].forEach(function (groupOpts, i) {
		    $("#products #sortable" + (i + 1))[0].Sortable = Sortable.create($("#products #sortable" + (i + 1))[0], {
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
	        var value = this.$("#products .sortable-search input").val().trim();
	        var lower = value.toLowerCase();
	        var showElements = new Array();
	        var hideElements = new Array();
	        $("#products #sortable2 li").each(function(i, el){
	            if($(el).text().toLowerCase().indexOf(lower) !== -1 || value == ""){
	                showElements.push(el);
	            }
	            else{
	                hideElements.push(el);
	            }
	        });
	        $(showElements).show();
	        $(hideElements).hide();
	    };
	    
	    this.$("#products .sortable-search input").change(changeFn.bind(this));
	    this.$("#products .sortable-search input").keyup(changeFn.bind(this));
    },

    importBibTeX: function(){
        this.bibtexDialog.dialog('open');
    },
    
    render: function(){
        if(this.model.isNew()){
            main.set('title', 'New Bibliography');
        }
        else {
            main.set('title', 'Edit Bibliography');
        }
        this.$el.html(this.template(this.model.toJSON()));
        this.renderProductsWidget();
        this.renderEditorsWidget();

        this.bibtexDialog = this.$("#bibtexDialog").dialog({
            autoOpen: false,
            modal: true,
            show: 'fade',
            resizable: false,
            draggable: false,
            width: "800px",
            open: function(){
                $("html").css("overflow", "hidden");
            },
            beforeClose: function(){
                $("html").css("overflow", "auto");
            },
            buttons: {
                "Import": function(e){
                    var button = $(e.currentTarget);
                    button.prop("disabled", true);
                    var value = $("textarea[name=bibtex]", this.bibtexDialog).val();
                    var overwrite = $("input[name=overwrite]:checked", this.bibtexDialog).val();
                    $("div.throbber", this.bibtexDialog).show();
                    $.post(wgServer + wgScriptPath + "/index.php?action=api.importBibTeX", {bibtex: value, overwrite: overwrite, private: 'no'}, function(response){
                        var data = response.data;
                        if(!_.isUndefined(data.created)){
                            var ids = _.pluck(data.created, 'id');
                            var oldIds = this.model.get('products');
                            var duplicateIds = _.pluck(data.duplicates, 'id');
                            var allIds = _.union(oldIds, ids, duplicateIds);
                            var strAllIds = new Array();
                            for (var i=0; i<allIds.length; ++i) {
                                strAllIds.push(allIds[i].toString());
                            }

                            this.model.set('products', strAllIds);
                            this.allProducts.add(data.created);
                            this.renderProductsWidget();
                        }
                        clearAllMessages();
                        if(response.errors.length > 0){
                            addError(response.errors.join("<br />"));
                        }
                        if(!_.isUndefined(data.created)){
                            var nCreated = data.created.length;
                            var nError = response.messages.length;
                            
                            if(nCreated > 0){
                                addSuccess("<b>" + nCreated + "</b> " + productsTerm.pluralize().toLowerCase() + " were created/updated");
                            }
                            if(nError > 0){
                                addInfo("<b>" + nError + "</b> " + productsTerm.pluralize().toLowerCase() + " were ignored (probably duplicates)");
                            }
                        }
                        button.prop("disabled", false);
                        $("div.throbber", this.bibtexDialog).hide();
                        this.bibtexDialog.dialog('close');
                    }.bind(this)).fail(function(){
                        clearAllMessages();
                        addError("There was an error importing the BibTeX references");
                        button.prop("disabled", false);
                        $("div.throbber", this.bibtexDialog).hide();
                        this.bibtexDialog.dialog('close');
                    }.bind(this));
                }.bind(this),
                "Cancel": function(){
                    this.bibtexDialog.dialog('close');
                }.bind(this)    
            }
            
        });

        return this.$el;
    }

});
