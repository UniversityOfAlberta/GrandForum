ManageProductsView = Backbone.View.extend({

    category: null,
    onlyRecent: true,
    hideExcluded: true,
    products: null,
    table: null,
    subViews: new Array(),
    editDialog: null,
    deleteDialog: null,
    deletePrivateDialog: null,
    ccvDialog: null,
    bibtexDialog: null,
    orcidDialog: null,
    crossForumExportDialog: null,
    duplicatesDialog: null,
    colVisDialog: null,

    initialize: function(options){
        this.subViews = new Array();
        if(options.category != undefined){
            this.category = options.category;
        }
        this.template = _.template($('#manage_products_template').html());
        this.listenTo(this.model, "sync", function(){
            this.products = this.model.getAll();
            this.listenTo(this.products, "add", this.addRows);
            this.listenTo(this.products, "remove", this.addRows);
            this.listenToOnce(this.products, "sync", function(){
                this.render();
            }.bind(this));
            this.duplicatesDialog = new DuplicatesDialogView(this.products);
        }, this);
    },
    
    addProduct: function(){
        var model = new Product({authors: [_.extend({fullname: me.get('fullName')}, me.toJSON())]});
        var view = new ProductEditView({el: this.editDialog, model: model, isDialog: true});
        var title = "Add " + productsTerm;
        if(this.category != null){
            title = "Add " + this.category;
            model.set('category', this.category);
        }
        this.editDialog.view = view;
        this.editDialog.dialog({
            height: $(window).height()*0.75, 
            width: 800,
            title: title
        });
        this.editDialog.dialog('open');
    },
    
    addFromDOI: function(){
        $("input[name=doi]", this.doiDialog).val('');
        this.doiDialog.dialog('open');
    },
    
    uploadCCV: function(){
        this.ccvDialog.dialog('open');
    },
    
    importBibTeX: function(){
        $("textarea[name=bibtex]", this.bibtexDialog).val('');
        this.bibtexDialog.dialog('open');
    },
    
    importOrcid: function(){
        this.orcidDialog.dialog('open');
    },

    crossForumExport: function(){
        this.crossForumExportDialog.dialog('open');
    },
    
    uploadCalendar: function(){
        this.calendarDialog.dialog('open');
    },
    
    currentProducts: function(){
        // Returns the current list of products that should be displayed in the table
        var products = this.products;
        if(this.category != null){
            products = new Products(products.where({category: this.category}));
        }
        if(this.onlyRecent){
            products = new Products(products.filter(function(p){
                return ((p.get('date') >= (YEAR - 2) + "-04-01") || (p.get('date') == ZOT) || (p.get('acceptance_date') >= (YEAR - 2) + "-04-01"));
            }));
        }
        if(this.hideExcluded){
            products = new Products(products.filter(function(p){
                return (!p.get('exclude') || p.dirty);
            }));
        }
        return products;
    },
    
    productChanged: function(){
        var products = this.currentProducts();
    
        // Count how many products there are dirty
        var sum = 0;
        products.each(function(product){
            if(product.dirty){
                sum++;
            }
        });
        this.$("#saveN").html("(" + sum + ")");
        if(sum == 0){
            this.$("#saveProducts").prop("disabled", true);
        }
        else{
            this.$("#saveProducts").prop("disabled", false);
        }
        if(sum > 0){
            window.onbeforeunload = function(){
                return "You have unsaved " + productsTerm.pluralize();
            }
        }
        else{
            window.onbeforeunload = null;
        }
        
        // Count how many products are private
        var sum = 0;
        products.each(function(product){
            if(product.get('access_id') > 0){
                sum++;
            }
        });
        this.$("#privateN").html("(" + sum + ")");
        this.$("#releaseN").html("(" + sum + ")");
        if(sum == 0){
            this.$("#deletePrivate").prop("disabled", true);
            this.$("#releasePrivate").prop("disabled", true);
        }
        else{
            this.$("#deletePrivate").prop("disabled", false);
            this.$("#releasePrivate").prop("disabled", false);
        }
    },
    
    addRows: function(){
        var searchStr = "";
        var order = [[4, 'desc'], // Date
                     [3, 'desc']]; // Acceptance Date
        if(this.table != undefined){
            order = this.table.order();
            searchStr = this.table.search();
            this.table.destroy();
            this.table = null;
        }
        
        var products = this.currentProducts();
        
        // First remove deleted models
        
        _.each(this.subViews, function(view){
            var m = view.model;
            if(products.where({id: m.get('id')}).length == 0){
                this.subViews = _.without(this.subViews, view);
                this.stopListening(m);
                view.$el.detach();
            }
        }.bind(this));
        
        // Then add new ones
        var models = _.pluck(_.pluck(this.subViews, 'model'), 'id');
        var frag = document.createDocumentFragment();
        
        products.each(function(p, i){
            if(!_.contains(models, p.id)){
                // Product isn't in the table yet
                this.listenTo(p, "dirty", this.productChanged);
                if(p.dirty == undefined){
                    p.dirty = false;
                }
                var row = new ManageProductsViewRow({model: p, parent: this});
                this.subViews.push(row);
                frag.appendChild(row.el);
            }
        }.bind(this));
        
        _.each(this.subViews, function(row){
            row.render();
        });
        this.$("#productRows").append(frag);
        
        this.createDataTable(order, searchStr);
        
        this.productChanged();
        this.$("#listTable").show();
        this.table.draw();
        
        if(this.category == "Presentation"){
            this.$("#presentationMessage").show();
        }
        else{
            this.$("#presentationMessage").hide();
        }
    },
    
    cacheRows: function(){
        // Needed so that the search functionality can be updated
        if(this.table != null){
            var rows = this.table.rows().indexes();
            var table = this.table;
            rows.each(function(i, val){
                if(this.subViews[i] != undefined){
                    this.subViews[i].row = this.table.row(i);
                }
            }.bind(this));
        }
    },    
    
    createDataTable: function(order, searchStr){
        var creating = true;
        this.table = this.$('#listTable').DataTable({'autoWidth': false,
                                                     'fixedHeader': true,
                                                     'preDrawCallback': function(){
                                                        return !creating;
                                                     },
                                                     'drawCallback': function(){
                                                        $("input", this.colVisDialog).each(function(i, check){
                                                            if($(check).is(":checked")){
                                                                $("table.dataTable tr td:nth-child(" + (i+1) + ")," +
                                                                  "table.dataTable tr th:nth-child(" + (i+1) + ")").each(function(j, el){
                                                                    el.style.display = '';
                                                                  }.bind(this));
                                                            }
                                                            else{
                                                                $("table.dataTable tr td:nth-child(" + (i+1) + ")," +
                                                                  "table.dataTable tr th:nth-child(" + (i+1) + ")").each(function(j, el){
                                                                    el.style.display = 'none';
                                                                  }.bind(this));
                                                            }
                                                        }.bind(this));
                                                     }.bind(this),
                                                     'aoColumnDefs': [
                                                        {'bSortable': false, 'aTargets': _.range(0, 1) }
                                                     ],
	                                                 'aLengthMenu': [[100], ['100']]});
	    creating = false;
	    this.cacheRows();
	    this.table.order(order);
	    this.table.search(searchStr);
	    this.$('#listTable_wrapper #listTable_length').remove();
	    this.$('#listTable_wrapper').prepend("<div id='listTable_length' class='dataTables_length'></div>");
	    this.$("#listTable_length").empty();
	    this.$("#listTable_length").append('<button id="saveProducts">Save All <span id="saveN">(0)</span></button>');
	    this.$("#listTable_length").append('<button id="deletePrivate">Delete All Private <span id="privateN">(0)</span></button>');
        this.$("#listTable_length").append('<button id="releasePrivate">Release All Private <span id="releaseN">(0)</span></button>');
	    this.$("#listTable_length").append('<span style="display:none;" class="throbber"></span>');
	    this.$("#listTable_length").append('<span id="showOnly">Show Only: <select><option value="">All</option></select></span>');
	    _.each(productStructure.categories, function(cat, key){
	        var el = $("<option value='" + key + "'>" + key.pluralize() + "</option>");
	        if(this.category == key){
	            el.prop('selected', true);
	        }
	        this.$("#showOnly select").append(el);
	    }.bind(this));
	    this.createColVis();
    },
    
    createColVis: function(){
        if(this.colVisDialog == null){
            this.$("#listTable thead tr th").each(function(i, th){
                var text = $(th).contents().filter(function(){ return this.nodeType == 3; }).text();
                this.$("#colvis").append("<input id='" + i + "' type='checkbox' value='" + text + "' style='vertical-align: bottom;' checked />&nbsp;" + text + "<br />");
            }.bind(this));
            this.$("#colvis input").change(function(){
                this.table.draw();
            }.bind(this));
            this.colVisDialog = this.$("#colvis").dialog({
                autoOpen: false,
	            show: "blind",
	            hide: "blind",
	            resizable: false,
	            draggable: false,
	            position: { my: "right top", at: "right bottom", of: this.$("#openColVis") }
            });
            if(this.category == "" || this.category == null){
                $("input[value=Category]", this.colVisDialog).prop('checked', true);
            }
            else{
                $("input[value=Category]", this.colVisDialog).prop('checked', false);
            }
        }
    },
    
    openColVis: function(){
        if(!this.colVisDialog.dialog('isOpen')){
            this.colVisDialog.dialog('open');
        }
        else{
            this.colVisDialog.dialog('close');
        }
    },
    
    deletePrivate: function(){
        this.deletePrivateDialog.dialog('open');
    },
    
    releasePrivate: function(){
        this.$("#saveProducts").prop('disabled', true);
        this.$("#listTable_length .throbber").show();
        var xhrs = new Array();
        var products = this.currentProducts();
        products.each(function(product){
            if(product.get('access_id') > 0){
                product.set('access_id', 0);
                var duplicates = product.getDuplicates();
                xhrs.push(duplicates.ready());
            }
        });
        $.when.apply(null, xhrs).done(function(){
            xhrs = new Array();
            var duplicateProducts = new Array();
            this.products.each(function(product){
                if(product.dirty){
                    if(product.duplicates.length > 0){
                        var newDuplicates = new Array();
                        product.duplicates.each(function(dupe){
                            var myProduct = this.products.findWhere({id: dupe.get('id')});
                            if(myProduct != undefined){
                                // This product is in my table
                                newDuplicates.push(myProduct);
                            }
                            else{
                                // This product is someone else's
                                newDuplicates.push(dupe);
                            }
                        }.bind(this));
                        product.duplicates.reset(newDuplicates);
                        duplicateProducts.push(product);
                    }
                    else{
                        // Save all Dirty Products
                        xhrs.push(product.save({}, {
                            success: function(){
                                // Save was successful, mark it as 'clean'
                                product.dirty = false;
                            }
                        }));
                    }
                }
            }.bind(this));
            if(duplicateProducts.length > 0){
                this.duplicatesDialog.model = duplicateProducts;
                this.duplicatesDialog.open();
            }
            $.when.apply(null, xhrs).done(function(){
                // Success
                clearAllMessages();
                addSuccess("All private " + productsTerm.pluralize().toLowerCase() + " have been successfully released");
                this.$("#saveProducts").prop('disabled', false);
                this.$("#listTable_length .throbber").hide();
                this.productChanged();
                this.addRows();
            }.bind(this)).fail(function(e){
                // Failure
                clearAllMessages();
                var list = new Array();
                list.push("There was a problem saving the following " + productsTerm.pluralize().toLowerCase() + ":<ul>");
                this.products.each(function(product){
                    if(product.dirty){
                        list.push("<li>" + product.get('title') + "</li>");
                    }
                });
                list.push("</ul>");
                addError(list.join(''));
                this.$("#saveProducts").prop('disabled', false);
                this.$("#listTable_length .throbber").hide();
                this.productChanged();
            }.bind(this));
        }.bind(this));
    },
    
    saveProducts: function(){
        var error = false;
        this.products.each(function(product){
            if(product.get('title').trim() == ""){
                error = true;
            }
        });
        if(error){
            addError("There is a product without a title");
            return;
        }
        this.$("#saveProducts").prop('disabled', true);
        this.$("#listTable_length .throbber").show();
        var xhrs = new Array();
        this.products.each(function(product){
            if(product.dirty){
                var duplicates = product.getDuplicates();
                xhrs.push(duplicates.ready());
            }
        });
        $.when.apply(null, xhrs).done(function(){
            xhrs = new Array();
            var duplicateProducts = new Array();
            this.products.each(function(product){
                if(product.dirty){
                    if(product.duplicates.length > 0){
                        var newDuplicates = new Array();
                        product.duplicates.each(function(dupe){
                            var myProduct = this.products.findWhere({id: dupe.get('id')});
                            if(myProduct != undefined){
                                // This product is in my table
                                newDuplicates.push(myProduct);
                            }
                            else{
                                // This product is someone else's
                                newDuplicates.push(dupe);
                            }
                        }.bind(this));
                        product.duplicates.reset(newDuplicates);
                        duplicateProducts.push(product);
                    }
                    else{
                        // Save all Dirty Products
                        xhrs.push(product.save({}, {
                            success: function(){
                                // Save was successful, mark it as 'clean'
                                product.dirty = false;
                            }
                        }));
                    }
                }
            }.bind(this));
            if(duplicateProducts.length > 0){
                this.duplicatesDialog.model = duplicateProducts;
                this.duplicatesDialog.open();
            }
            $.when.apply(null, xhrs).done(function(){
                // Success
                clearAllMessages();
                addSuccess("All " + productsTerm.pluralize().toLowerCase() + " have been successfully saved");
                this.$("#saveProducts").prop('disabled', false);
                this.$("#listTable_length .throbber").hide();
                this.productChanged();
                this.addRows();
            }.bind(this)).fail(function(e){
                // Failure
                clearAllMessages();
                var list = new Array();
                list.push("There was a problem saving the following " + productsTerm.pluralize().toLowerCase() + ":<ul>");
                this.products.each(function(product){
                    if(product.dirty){
                        list.push("<li>" + product.get('title') + "</li>");
                    }
                });
                list.push("</ul>");
                addError(list.join(''));
                this.$("#saveProducts").prop('disabled', false);
                this.$("#listTable_length .throbber").hide();
                this.productChanged();
            }.bind(this));
        }.bind(this));
    },
    
    showOnly: function(){
        this.category = this.$("#showOnly select > option:selected").val();
        if(this.category == ''){
            this.category = null;
            main.set('title', productsTerm + ' Management');
            this.$("#addProductButton").text("Add " + productsTerm);
            $("input[value=Category]", this.colVisDialog).prop('checked', true);
        }
        else{
            main.set('title', this.category.pluralize() + ' Management');
            this.$("#addProductButton").text("Add " + this.category);
            $("input[value=Category]", this.colVisDialog).prop('checked', false);
        }
        this.addRows();
    },
    
    changeRecent: function(){
        if(this.onlyRecent != $("#onlyRecent")){
            this.onlyRecent = $("#onlyRecent").is(":checked");
            _.defer(this.addRows.bind(this));
        }
    },
    
    changeHideExcluded: function(){
        if(this.hideExcluded != $("#hideExcluded")){
            this.hideExcluded = $("#hideExcluded").is(":checked");
            _.defer(this.addRows.bind(this));
        }
    },
    
    events: {
        "click #saveProducts": "saveProducts",
        "click #deletePrivate": "deletePrivate",
        "click #releasePrivate": "releasePrivate",
        "click #addProductButton": "addProduct",
        "click #addFromDOIButton": "addFromDOI",
        "click #uploadCCVButton": "uploadCCV",
        "click #importBibTexButton": "importBibTeX",
        "click #uploadCalendarButton": "uploadCalendar",
        "click #importOrcidButton": "importOrcid",
        "click #crossForumExport": "crossForumExport",
        "click #openColVis": "openColVis",
        "change #showOnly select": "showOnly",
        "change #onlyRecent": "changeRecent",
        "change #hideExcluded": "changeHideExcluded"
    },
    
    render: function(){
        this.$el.empty();
        $(document).click(function(e){
            var popup = $("div.popupBox:visible").not(":animated").first();
            if(popup.length > 0 && !$.contains(popup[0], e.target)){
                _.each(this.subViews, function(view){
                    if(view.$("div.popupBox").is(":visible")){
                        // Need to defer the event so that unchecking a project is not in conflict
                        _.defer(function(){
                            view.model.trigger("change", view.model);
                        });
                    }
                });
            }
        }.bind(this));
        this.$el.html(this.template());
        main.bind('change:title', function(){
            $("#pageTitle").append("&nbsp;<span class='clicktooltip' title=''>&#9432;</span>");
            $("#pageTitle .clicktooltip").attr('title', this.$('#instructions').html());
            $("#pageTitle .clicktooltip").qtip({
	            position: {
	                adjust: {
		                x: -($("#pageTitle .clicktooltip").width()/25),
		                y: -($("#pageTitle .clicktooltip").height()/2)
	                }
	            },
	            style: {
	                classes: "instructions-qtip"
	            },
	            show: 'click',
                hide: 'click unfocus'
	        });
        }.bind(this));
        main.trigger('change:title');
        this.addRows();
	    var maxWidth = 50;
	    this.$('.angledTableText').each(function(i, e){
	        maxWidth = Math.max(maxWidth, $(e).width());
	    });
	    this.$('.angledTableHead').height(maxWidth +"px");
	    this.$('.angledTableHead').width('40px');
	    this.productChanged();
	    this.editDialog = this.$("#editDialog").dialog({
	        autoOpen: false,
	        modal: true,
	        show: 'fade',
	        resizable: false,
	        draggable: false,
	        open: function(){
	            $("html").css("overflow", "hidden");
	        },
	        beforeClose: function(){
	            this.editDialog.view.stopListening();
	            this.editDialog.view.undelegateEvents();
	            this.editDialog.view.$el.empty();
	            this.editDialog.view.teardown();
	            $("html").css("overflow", "auto");
	        }.bind(this),
	        buttons: [
	            {
	                text: "Save " + productsTerm,
	                click: function(){
                        var validation = this.editDialog.view.validate();
                        if(validation != ""){
                            clearAllMessages("#dialogMessages");
                            addError(validation, true, "#dialogMessages");
                            return "";
                        }
                        $("button", this.editDialog.view.$el.parent()).prop("disabled", true);
                        $("button", this.editDialog.view.$el.parent()).before("<span id='saveThrobber' class='throbber'></span>&nbsp;");
                        this.editDialog.view.model.save(null, {
                            success: function(){
                                var product = this.editDialog.view.model;
                                var duplicates = product.getDuplicates();
                                $.when(duplicates.ready()).done(function(){
                                    $("button", this.editDialog.view.$el.parent()).prop("disabled", false);
                                    $("#saveThrobber", this.editDialog.view.$el.parent()).remove();
                                    product.dirty = false;
                                    this.editDialog.dialog("close");
                                    var duplicateProducts = new Array();
                                    // First make sure that there are no duplicates
                                    if(product.duplicates.length > 0){
                                        // This product has duplicates
                                        var newDuplicates = new Array();
                                        product.duplicates.each(function(dupe){
                                            var myProduct = this.products.findWhere({id: dupe.get('id')});
                                            if(myProduct != undefined){
                                                // This product is in my table
                                                newDuplicates.push(myProduct);
                                            }
                                            else{
                                                // This product is someone else's
                                                newDuplicates.push(dupe);
                                            }
                                        }.bind(this));
                                        product.duplicates.reset(newDuplicates);
                                        duplicateProducts.push(product);
                                    }
                                    else{
                                        // No Duplicates so show success!
                                        clearAllMessages();
                                        addSuccess("The " + productsTerm + " has been saved sucessfully");
                                        if(this.products.indexOf(this.editDialog.view.model) == -1){
                                            this.products.add(this.editDialog.view.model);
                                        }
                                    }
                                    if(duplicateProducts.length > 0){
                                        this.duplicatesDialog.model = duplicateProducts;
                                        this.duplicatesDialog.open();
                                    }
                                }.bind(this));
                            }.bind(this),
                            error: function(o, e){
                                $("#saveThrobber", this.editDialog.view.$el.parent()).remove();
                                $("button", this.editDialog.view.$el.parent()).prop("disabled", false);
                                clearAllMessages("#dialogMessages");
                                if(e.responseText != ""){
                                    addError(e.responseText, true, "#dialogMessages");
                                }
                                else{
                                    addError("There was a problem saving the " + productsTerm, true, "#dialogMessages");
                                }
                            }.bind(this)
                        });
                    }.bind(this)
                }
            ]
	    });
	    this.deleteDialog = this.$("#deleteDialog").dialog({
	        autoOpen: false,
	        modal: true,
	        show: 'fade',
	        resizable: false,
	        draggable: false,
	        open: function(){
	            $("html").css("overflow", "hidden");
	            $(".ui-dialog-buttonpane button:contains('Yes')", this.deleteDialog.parent()).prop("disabled", true);
	            $("#deleteCheck", this.deleteDialog).prop("checked", false);
	            $("#deleteCheck", this.deleteDialog).change(function(e){
	                var isChecked = $(e.currentTarget).is(":checked");
	                if(isChecked){
	                    $(".ui-dialog-buttonpane button:contains('Yes')", this.deleteDialog.parent()).prop("disabled", false);
	                }
	                else{
	                    $(".ui-dialog-buttonpane button:contains('Yes')", this.deleteDialog.parent()).prop("disabled", true);
	                }
	            }.bind(this));
	        }.bind(this),
	        beforeClose: function(){
	            $("html").css("overflow", "auto");
	        },
	        buttons: {
	            "Yes": function(){
	                var model = this.deleteDialog.model;
	                if(model.get('deleted') != true){
	                    $("div.throbber", this.deleteDialog).show();
                        model.destroy({
                            success: function(model, response) {
                                this.deleteDialog.dialog('close');
                                $("div.throbber", this.deleteDialog).hide();
                                if(response.deleted == true){
                                    model.set(response);
                                    clearSuccess();
                                    clearError();
                                    addSuccess('The ' + response.category + ' <i>' + response.title + '</i> was deleted sucessfully');
                                }
                                else{
                                    clearSuccess();
                                    clearError();
                                    addError('The ' + response.category + ' <i>' + response.title + '</i> was not deleted sucessfully');
                                }
                            }.bind(this),
                            error: function(model, response) {
                                this.deleteDialog.dialog('close');
                                clearSuccess();
                                clearError();
                                addError('The ' + response.category + ' <i>' + response.title + '</i> was not deleted sucessfully');
                            }.bind(this)
                        });
                    }
                    else{
                        this.deleteDialog.dialog('close');
                        clearAllMessages();
                        addError('This ' + model.get('category') + ' is already deleted');
                    }
	            }.bind(this),
	            "No": function(){
	                this.deleteDialog.dialog('close');
	            }.bind(this)
	        }
	    });
	    this.deletePrivateDialog = this.$("#deletePrivateDialog").dialog({
	        autoOpen: false,
	        modal: true,
	        show: 'fade',
	        resizable: false,
	        draggable: false,
	        open: function(){
	            $("html").css("overflow", "hidden");
	            $(".ui-dialog-buttonpane button:contains('Yes')", this.deletePrivateDialog.parent()).prop("disabled", true);
	            $("#deleteCheck", this.deletePrivateDialog).prop("checked", false);
	            $("#deleteCheck", this.deletePrivateDialog).change(function(e){
	                var isChecked = $(e.currentTarget).is(":checked");
	                if(isChecked){
	                    $(".ui-dialog-buttonpane button:contains('Yes')", this.deletePrivateDialog.parent()).prop("disabled", false);
	                }
	                else{
	                    $(".ui-dialog-buttonpane button:contains('Yes')", this.deletePrivateDialog.parent()).prop("disabled", true);
	                }
	            }.bind(this));
	        }.bind(this),
	        beforeClose: function(){
	            $("html").css("overflow", "auto");
	        },
	        buttons: {
	            "Yes": function(e){
	                var button = $(e.currentTarget);
	                button.prop("disabled", true);
                    var xhrs = new Array();
                    var toDelete = new Array();
                    var products = this.currentProducts();
                    products.each(function(product){
                        if(product.get('access_id') > 0){
                            toDelete.push(product);
                        }
                    });
                    $("div.throbber", this.deletePrivateDialog).show();
                    _.each(toDelete, function(product){
                        xhrs.push(product.destroy({silent: true}));
                    });
                    $.when.apply(null, xhrs).done(function(){
                        // Success
                        clearAllMessages();
                        addSuccess("All private " + productsTerm.pluralize().toLowerCase() + " have been successfully deleted");
                        this.addRows();
                        button.prop("disabled", false);
                        $("div.throbber", this.deletePrivateDialog).hide();
                        this.deletePrivateDialog.dialog('close');
                    }.bind(this)).fail(function(e){
                        // Failure
                        $("div.throbber", this.deletePrivateDialog).hide();
                        clearAllMessages();
                        var list = new Array();
                        list.push("There was a problem deleting the following " + productsTerm.pluralize().toLowerCase() + ":<ul>");
                        products.each(function(product){
                            if(product.get('access_id') > 0){
                                list.push("<li>" + product.get('title') + "</li>");
                            }
                        });
                        list.push("</ul>");
                        addError(list.join(''));
                        this.addRows();
                        button.prop("disabled", false);
                        $("div.throbber", this.deletePrivateDialog).hide();
                        this.deletePrivateDialog.dialog('close');
                    }.bind(this));
	            }.bind(this),
	            "No": function(){
	                this.deletePrivateDialog.dialog('close');
	            }.bind(this)
	        }
	    });
	    this.ccvDialog = this.$("#ccvDialog").dialog({
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
	            "Upload": function(e){
	                var button = $(e.currentTarget);
	                button.prop("disabled", true);
	                $("div.throbber", this.ccvDialog).show();
	                ccvUploaded = function(response, error){
	                    // Purposefully global so that iframe can access
	                    if(error == undefined || error == ""){
	                        if(!_.isUndefined(response.created)){
	                            var ids = _.pluck(response.created, 'id');
	                            this.products.remove(ids, {silent: true});
	                            this.products.trigger("remove");
                                this.products.add(response.created, {silent: true});
                                this.products.trigger("add");
                            }
	                        //this.products.add(response.created, {silent: true});
	                        //this.addRows();
	                        clearAllMessages();
                            var nCreated = response.created.length;
                            var nError = response.error.length;
                            if(nCreated > 0){
                                var info = "<b>" + nCreated + "</b> " + productsTerm.pluralize().toLowerCase() + " were created/updated<br />" +
                                           "<a style='cursor:pointer;' onClick='$(\"#createdOutputs\").slideDown();$(this).hide();'>Show " + productsTerm.pluralize().toLowerCase() + "<br /></a>" +
                                           "<div id='createdOutputs' style='max-height:200px; overflow-y:auto; display:none;'><ul>" + 
                                           "<li>" + _.pluck(response.created, 'title').join("</li><li>") + 
                                           "</li></ul></div>";
                                addSuccess(info);
	                        }
	                        if(nError > 0){
	                            var info = "<b>" + nError + "</b> " + productsTerm.pluralize().toLowerCase() + " were ignored (probably duplicates)<br />" +
                                           "<a style='cursor:pointer;' onClick='$(\"#duplicateOutputs\").slideDown();$(this).hide();'>Show " + productsTerm.pluralize().toLowerCase() + "<br /></a>" +
                                           "<div id='duplicateOutputs' style='max-height:200px; overflow-y:auto; display:none;'><ul>" + 
                                           "<li>" + _.pluck(response.error, 'title').join("</li><li>") + 
                                           "</li></ul></div>";
                                addInfo(info);
	                        }
	                        button.prop("disabled", false);
	                        $("div.throbber", this.ccvDialog).hide();
	                        this.ccvDialog.dialog('close');
	                    }
	                    else{
	                        button.prop("disabled", false);
	                        $("div.throbber", this.ccvDialog).hide();
	                        clearAllMessages();
	                        addError(error);
	                        this.ccvDialog.dialog('close');
	                    }
	                }.bind(this);
	                var form = $("form", this.ccvDialog);
	                form.submit();
	            }.bind(this),
	            "Cancel": function(){
	                this.ccvDialog.dialog('close');
	            }.bind(this)
	        }
	    });
	    this.orcidDialog = this.$("#orcidDialog").dialog({
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
	                var importOrcidBibtex = function(){
	                    $.post(wgServer + wgScriptPath + "/index.php?action=api.importORCID", {overwrite: overwrite}, function(response){
                            var data = response.data;
                            if(!_.isUndefined(data.created)){
                                var ids = _.pluck(data.created, 'id');
                                this.products.remove(ids, {silent: true});
                                this.products.trigger("remove");
                                this.products.add(data.created, {silent: true});
                                this.products.trigger("add");
                            }
                            clearAllMessages();
                            if(response.errors.length > 0){
                                if(response.errors[0] == "Invalid Access Token"){
                                    $.removeCookie('orcid');
                                    $.removeCookie('access_token');
                                    authorizeOrcid();
                                    return;
                                }
                                addError(response.errors.join("<br />"));
                            }
                            if(!_.isUndefined(data.created)){
                                var nCreated = data.created.length;
                                var nError = response.messages.length;
                                
                                if(nCreated > 0){
                                    var info = "<b>" + nCreated + "</b> " + productsTerm.pluralize().toLowerCase() + " were created/updated<br />" +
                                               "<a style='cursor:pointer;' onClick='$(\"#createdOutputs\").slideDown();$(this).hide();'>Show " + productsTerm.pluralize().toLowerCase() + "<br /></a>" +
                                               "<div id='createdOutputs' style='max-height:200px; overflow-y:auto; display:none;'><ul>" + 
                                               "<li>" + _.pluck(response.data.created, 'title').join("</li><li>") + 
                                               "</li></ul></div>";
                                    addSuccess(info);
                                }
                                if(nError > 0){
                                    var info = "<b>" + nError + "</b> " + productsTerm.pluralize().toLowerCase() + " were ignored (probably duplicates)<br />" +
                                               "<a style='cursor:pointer;' onClick='$(\"#duplicateOutputs\").slideDown();$(this).hide();'>Show " + productsTerm.pluralize().toLowerCase() + "<br /></a>" +
                                               "<div id='duplicateOutputs' style='max-height:200px; overflow-y:auto; display:none;'><ul>" + 
                                               "<li>" + response.messages.join("</li><li>") + 
                                               "</li></ul></div>";
                                    addInfo(info);
                                }
                            }
                            button.prop("disabled", false);
                            $("div.throbber", this.orcidDialog).hide();
                            this.orcidDialog.dialog('close');
                        }.bind(this)).fail(function(){
                            clearAllMessages();
                            addError("There was an error importing the ORCID publications");
                            button.prop("disabled", false);
                            $("div.throbber", this.orcidDialog).hide();
                            this.orcidDialog.dialog('close');
                        }.bind(this));
	                }.bind(this);
	                
	                var authorizeOrcid = function(){
	                    if($.cookie('orcid') == undefined && $.cookie('access_token') == undefined){
	                        var url = "https://orcid.org/oauth/authorize?client_id=" + orcidId + "&response_type=code&scope=/read-limited&redirect_uri=" + document.location.origin + document.location.pathname;
                            //var url = "https://orcid.org/oauth/authorize?client_id=" + orcidId + "&response_type=code&scope=/read-limited&redirect_uri=" + document.location.origin + document.location.pathname;
                            var popup = window.open(url,'popUpWindow','height=600,width=500,left=100,top=100,resizable=yes,scrollbars=yes,toolbar=yes,menubar=no,location=no,directories=no, status=yes');
                            var popupInterval = setInterval(function(){
                                if(popup == null || popup.closed){
                                    importOrcidBibtex();
                                    clearInterval(popupInterval);
                                }
                            }.bind(this), 500);
                        }
                        else{
                            importOrcidBibtex();
                        }
	                }.bind(this);
	                
	                var button = $(e.currentTarget);
	                button.prop("disabled", true);
	                var overwrite = $("input[name=orcid_overwrite]:checked", this.orcidDialog).val();
	                $("div.throbber", this.orcidDialog).show();
                    authorizeOrcid();
	                
	            }.bind(this),
	            "Cancel": function(){
	                this.orcidDialog.dialog('close');
	            }.bind(this)
	        }
	    });
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
	                var overwrite = $("input[name=bibtex_overwrite]:checked", this.bibtexDialog).val();
	                $("div.throbber", this.bibtexDialog).show();
	                $.post(wgServer + wgScriptPath + "/index.php?action=api.importBibTeX", {bibtex: value, overwrite: overwrite}, function(response){
	                    var data = response.data;
	                    if(!_.isUndefined(data.created)){
	                        var ids = _.pluck(data.created, 'id');
	                        this.products.remove(ids, {silent: true});
	                        this.products.trigger("remove");
                            this.products.add(data.created, {silent: true});
                            this.products.trigger("add");
                        }
                        clearAllMessages();
                        if(response.errors.length > 0){
                            addError(response.errors.join("<br />"));
                        }
                        if(!_.isUndefined(data.created)){
                            var nCreated = data.created.length;
                            var nError = response.messages.length;
                            
                            if(nCreated > 0){
                                var info = "<b>" + nCreated + "</b> " + productsTerm.pluralize().toLowerCase() + " were created/updated<br />" +
                                           "<a style='cursor:pointer;' onClick='$(\"#createdOutputs\").slideDown();$(this).hide();'>Show " + productsTerm.pluralize().toLowerCase() + "<br /></a>" +
                                           "<div id='createdOutputs' style='max-height:200px; overflow-y:auto; display:none;'><ul>" + 
                                           "<li>" + _.pluck(response.data.created, 'title').join("</li><li>") + 
                                           "</li></ul></div>";
                                addSuccess(info);
                            }
                            if(nError > 0){
                                var info = "<b>" + nError + "</b> " + productsTerm.pluralize().toLowerCase() + " were ignored (probably duplicates)<br />" +
                                           "<a style='cursor:pointer;' onClick='$(\"#duplicateOutputs\").slideDown();$(this).hide();'>Show " + productsTerm.pluralize().toLowerCase() + "<br /></a>" +
                                           "<div id='duplicateOutputs' style='max-height:200px; overflow-y:auto; display:none;'><ul>" + 
                                           "<li>" + response.messages.join("</li><li>") + 
                                           "</li></ul></div>";
                                addInfo(info);
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
	    this.doiDialog = this.$("#doiDialog").dialog({
	        autoOpen: false,
	        modal: true,
	        show: 'fade',
	        resizable: false,
	        draggable: false,
	        width: "500px",
	        open: function(){
	            $("html").css("overflow", "hidden");
	        },
	        beforeClose: function(){
	            $("html").css("overflow", "auto");
	        },
	        buttons: [
	            {
	                text: "Save " + productsTerm,
	                click: function(e){
	                    var button = $(e.currentTarget);
	                    button.prop("disabled", true);
	                    var value = $("input[name=doi]", this.doiDialog).val();
	                    var overwrite = $("input[name=doi_overwrite]:checked", this.doiDialog).val();
	                    $("div.throbber", this.doiDialog).show();
	                    $.post(wgServer + wgScriptPath + "/index.php?action=api.importDOI", {doi: value, overwrite: overwrite}, function(response){
	                        var data = response.data;
	                        if(!_.isUndefined(data.created)){
	                            var ids = _.pluck(data.created, 'id');
	                            this.products.remove(ids);
                                this.products.add(data.created);
                            }
                            clearAllMessages();
                            if(response.errors.length > 0){
                                addError(response.errors.join("<br />"));
                            }
                            if(!_.isUndefined(data.created)){
                                var nCreated = data.created.length;
                                var nError = response.messages.length;
                                if(nCreated > 0){
                                    var info = "<b>" + nCreated + "</b> " + productsTerm.pluralize().toLowerCase() + " were created/updated<br />" +
                                               "<a style='cursor:pointer;' onClick='$(\"#createdOutputs\").slideDown();$(this).hide();'>Show " + productsTerm.pluralize().toLowerCase() + "<br /></a>" +
                                               "<div id='createdOutputs' style='max-height:200px; overflow-y:auto; display:none;'><ul>" + 
                                               "<li>" + _.pluck(response.data.created, 'title').join("</li><li>") + 
                                               "</li></ul></div>";
                                    addSuccess(info);
                                }
                                if(nError > 0){
                                    var info = "<b>" + nError + "</b> " + productsTerm.pluralize().toLowerCase() + " were ignored (probably duplicates)<br />" +
                                               "<a style='cursor:pointer;' onClick='$(\"#duplicateOutputs\").slideDown();$(this).hide();'>Show " + productsTerm.pluralize().toLowerCase() + "<br /></a>" +
                                               "<div id='duplicateOutputs' style='max-height:200px; overflow-y:auto; display:none;'><ul>" + 
                                               "<li>" + response.messages.join("</li><li>") + 
                                               "</li></ul></div>";
                                    addInfo(info);
                                }
                            }
                            button.prop("disabled", false);
                            $("div.throbber", this.doiDialog).hide();
                            this.doiDialog.dialog('close');
	                    }.bind(this)).fail(function(){
	                        clearAllMessages();
	                        addError("There was an error importing the DOI reference");
	                        button.prop("disabled", false);
                            $("div.throbber", this.doiDialog).hide();
                            this.doiDialog.dialog('close');
	                    }.bind(this));
	                }.bind(this)
	            },
	            {
	                text: "Cancel",
	                click: function(){
	                    this.doiDialog.dialog('close');
	                }.bind(this)
	            }
	        ]
	    });
	    this.calendarDialog = this.$("#calendarDialog").dialog({
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
	            "Upload": function(e){
	                var button = $(e.currentTarget);
	                button.prop("disabled", true);
	                $("div.throbber", this.calendarDialog).show();
	                icsUploaded = function(response, error){
	                    // Purposefully global so that iframe can access
	                    if(error == undefined || error == ""){
	                        this.products.add(response.created, {silent: true});
	                        this.addRows();
	                        clearAllMessages();
                            var nCreated = response.created.length;
                            var nError = response.error.length;
                            if(nCreated > 0){
	                            addSuccess("<b>" + nCreated + "</b> " + productsTerm.pluralize().toLowerCase() + " were created");
	                        }
	                        if(nError > 0){
	                            addInfo("<b>" + nError + "</b> " + productsTerm.pluralize().toLowerCase() + " were ignored (probably duplicates)");
	                        }
	                        button.prop("disabled", false);
	                        $("div.throbber", this.calendarDialog).hide();
	                        this.calendarDialog.dialog('close');
	                    }
	                    else{
	                        button.prop("disabled", false);
	                        $("div.throbber", this.calendarDialog).hide();
	                        clearAllMessages();
	                        addError(error);
	                        this.calendarDialog.dialog('close');
	                    }
	                }.bind(this);
	                var form = $("form", this.calendarDialog);
	                form.submit();
	            }.bind(this),
	            "Cancel": function(){
	                this.calendarDialog.dialog('close');
	            }.bind(this)
	        }
	    });
	    this.crossForumExportDialog = this.$("#crossForumExportDialog").dialog({
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
	                var value = $("select", this.crossForumExportDialog).val();
	                var overwrite = $("input[name=overwrite]:checked", this.crossForumExportDialog).val();
	                $("div.throbber", this.crossForumExportDialog).show();
	                var popup = openCrossForumExport(value, function(event){
	                    clearInterval(popupInterval);
	                    var bibtex = event.data;
	                    $.post(wgServer + wgScriptPath + "/index.php?action=api.importBibTeX", {bibtex: bibtex, overwrite: overwrite}, function(response){
	                        var data = response.data;
	                        if(!_.isUndefined(data.created)){
	                            var ids = _.pluck(data.created, 'id');
	                            this.products.remove(ids, {silent: true});
	                            this.products.trigger("remove");
                                this.products.add(data.created, {silent: true});
                                this.products.trigger("add");
                            }
                            clearAllMessages();
                            if(response.errors.length > 0){
                                addError(response.errors.join("<br />"));
                            }
                            if(!_.isUndefined(data.created)){
                                var nCreated = data.created.length;
                                var nError = response.messages.length;
                                
                                if(nCreated > 0){
                                    var info = "<b>" + nCreated + "</b> " + productsTerm.pluralize().toLowerCase() + " were created/updated<br />" +
                                               "<a style='cursor:pointer;' onClick='$(\"#createdOutputs\").slideDown();$(this).hide();'>Show " + productsTerm.pluralize().toLowerCase() + "<br /></a>" +
                                               "<div id='createdOutputs' style='max-height:200px; overflow-y:auto; display:none;'><ul>" + 
                                               "<li>" + _.pluck(response.data.created, 'title').join("</li><li>") + 
                                               "</li></ul></div>";
                                    addSuccess(info);
                                }
                                if(nError > 0){
                                    var info = "<b>" + nError + "</b> " + productsTerm.pluralize().toLowerCase() + " were ignored (probably duplicates)<br />" +
                                               "<a style='cursor:pointer;' onClick='$(\"#duplicateOutputs\").slideDown();$(this).hide();'>Show " + productsTerm.pluralize().toLowerCase() + "<br /></a>" +
                                               "<div id='duplicateOutputs' style='max-height:200px; overflow-y:auto; display:none;'><ul>" + 
                                               "<li>" + response.messages.join("</li><li>") + 
                                               "</li></ul></div>";
                                    addInfo(info);
                                }
                            }
                            button.prop("disabled", false);
                            $("div.throbber", this.crossForumExportDialog).hide();
                            this.crossForumExportDialog.dialog('close');
	                    }.bind(this)).fail(function(){
	                        clearAllMessages();
	                        addError("There was an error importing the BibTeX references");
	                        button.prop("disabled", false);
                            $("div.throbber", this.crossForumExportDialog).hide();
                            this.crossForumExportDialog.dialog('close');
	                    }.bind(this));
	                }.bind(this));
	                var popupInterval = setInterval(function(){
                        if(popup == null || popup.closed){
                            button.prop("disabled", false);
                            $("div.throbber", this.crossForumExportDialog).hide();
                            clearInterval(popupInterval);
                        }
                    }.bind(this), 500);
	            }.bind(this),
	            "Cancel": function(){
	                this.crossForumExportDialog.dialog('close');
	            }.bind(this)
	        }
	    });
	    $(window).resize(function(){
	        this.editDialog.dialog({height: $(window).height()*0.75});
	    }.bind(this));
        return this.$el;
    }

});
