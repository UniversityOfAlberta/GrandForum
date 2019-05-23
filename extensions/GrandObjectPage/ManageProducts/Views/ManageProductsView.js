ManageProductsView = Backbone.View.extend({

    allProjects: null,
    otherProjects: null,
    oldProjects: null,
    products: null,
    projects: null,
    table: null,
    nProjects: 0,
    subViews: new Array(),
    editDialog: null,
    deleteDialog: null,
    deletePrivateDialog: null,
    ccvDialog: null,
    bibtexDialog: null,
    duplicatesDialog: null,

    initialize: function(){
        this.subViews = new Array();
        this.allProjects = new Projects();
        this.allProjects.fetch();
        this.template = _.template($('#manage_products_template').html());
        me.getProjects();
        this.listenTo(this.model, "sync", function(){
            this.products = this.model.getAll();
            this.listenTo(this.products, "add", this.addRows);
            this.listenTo(this.products, "remove", this.addRows);
            this.listenToOnce(this.products, "sync", function(){
                me.projects.ready().then(function(){
                    this.projects = me.projects.getCurrent();
                    return this.projects.ready();
                }.bind(this)).then(function(){
                    return this.allProjects.ready();
                }.bind(this)).then(function(){
                    this.otherProjects = this.allProjects.getCurrent();
                    this.oldProjects = this.allProjects.getOld();
                    this.otherProjects.remove(this.projects.models);
                    this.oldProjects.remove(this.projects.models);
                    return me.projects.ready();
                }.bind(this)).then(function(){
                    this.render();
                }.bind(this));              
            }.bind(this));
            this.duplicatesDialog = new DuplicatesDialogView(this.products);
        }, this);
    },
    
    addProduct: function(){
        var model = new Product({authors: [me.toJSON()]});
        var view = new ProductEditView({el: this.editDialog, model: model, isDialog: true});
        this.editDialog.view = view;
        this.editDialog.dialog({
            height: $(window).height()*0.75, 
            width: 800,
            title: "Create " + productsTerm
        });
        this.editDialog.dialog('open');
    },
    
    addFromDOI: function(){
        this.doiDialog.dialog('open');
    },
    
    uploadCCV: function(){
        this.ccvDialog.dialog('open');
    },
    
    importBibTeX: function(){
        this.bibtexDialog.dialog('open');
    },
    
    uploadCalendar: function(){
        this.calendarDialog.dialog('open');
    },
    
    productChanged: function(){
        // Count how many products there are dirty
        var sum = 0;
        this.products.each(function(product){
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
        this.products.each(function(product){
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
        
        // Change the state of the 'selectAll' checkbox
        this.projects.each(function(project){
            var allFound = true;
            this.products.each(function(product){
                if(allFound && _.where(product.get('projects'), {id: project.get('id')}).length == 0){
                    allFound = false;
                }
            }, this);
            if(allFound){
                this.$("input.selectAll[data-project=" + project.get('id') + "]").prop('checked', true);
            }
            else{
                this.$("input.selectAll[data-project=" + project.get('id') + "]").prop('checked', false);
            }
        }, this);
    },
    
    addRows: function(){
        var searchStr = "";
        var order = [this.projects.length + 3, 'desc'];
        if(this.table != undefined){
            order = this.table.order();
            searchStr = this.table.search();
            this.table.destroy();
            this.table = null;
        }
        // First remove deleted models
        _.each(this.subViews, function(view){
            var m = view.model;
            if(this.products.where({id: m.get('id')}).length == 0){
                this.subViews = _.without(this.subViews, view);
                view.remove();
            }
        }.bind(this));
        // Then add new ones
        var models = _.pluck(_.pluck(this.subViews, 'model'), 'id');
        //var start = new Date().getTime();
        var frag = document.createDocumentFragment();
        this.products.each(function(p, i){
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
        //var end = new Date().getTime();
        //console.log(end - start);
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
        this.table = this.$('#listTable').DataTable({'bPaginate': false,
                                                     'autoWidth': false,
                                                     'aoColumnDefs': [
                                                        {'bSortable': false, 'aTargets': _.range(0, this.projects.length + 2) }
                                                     ],
	                                                 'aLengthMenu': [[-1], ['All']]});
	    this.cacheRows();
	    this.table.order(order);
	    this.table.search(searchStr);
	    this.table.draw();
	    this.$('#listTable_wrapper').prepend("<div id='listTable_length' class='dataTables_length'></div>");
	    this.$("#listTable_length").empty();
	    this.$("#listTable_length").append('<button id="saveProducts">Save All <span id="saveN">(0)</span></button>');
	    this.$("#listTable_length").append('<button id="deletePrivate">Delete All Private <span id="privateN">(0)</span></button>');
        this.$("#listTable_length").append('<button id="releasePrivate">Release All Private <span id="releaseN">(0)</span></button>');
	    this.$("#listTable_length").append('<span style="display:none;" class="throbber"></span>');
    },
    
    toggleSelect: function(e){
        var wrapper = this.$('#listTable_wrapper').detach();
        var target = $(e.currentTarget);
        var projectId = target.attr('data-project');
        var checked = target.is(":checked");
        _.each(this.subViews, function(view){
            if(checked){
                view.select(projectId);
            }
            else{
                view.unselect(projectId);
            }
        });
        wrapper.appendTo("#currentView");
        this.productChanged();
    },
    
    deletePrivate: function(){
        this.deletePrivateDialog.dialog('open');
    },
    
    releasePrivate: function(){
        this.$("#saveProducts").prop('disabled', true);
        this.$(".throbber").show();
        var xhrs = new Array();
        this.products.each(function(product){
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
                this.$(".throbber").hide();
                this.productChanged();
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
                this.$(".throbber").hide();
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
        this.$(".throbber").show();
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
                this.$(".throbber").hide();
                this.productChanged();
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
                this.$(".throbber").hide();
                this.productChanged();
            }.bind(this));
        }.bind(this));
    },
    
    events: {
        "click .selectAll": "toggleSelect",
        "click #saveProducts": "saveProducts",
        "click #deletePrivate": "deletePrivate",
        "click #releasePrivate": "releasePrivate",
        "click #addProductButton": "addProduct",
        "click #addFromDOIButton": "addFromDOI",
        "click #uploadCCVButton": "uploadCCV",
        "click #importBibTexButton": "importBibTeX",
        "click #uploadCalendarButton": "uploadCalendar"
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
                        this.editDialog.view.model.save(null, {
                            success: function(){
                                var product = this.editDialog.view.model;
                                var duplicates = product.getDuplicates();
                                $.when(duplicates.ready()).done(function(){
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
	        },
	        beforeClose: function(){
	            $("html").css("overflow", "auto");
	        },
	        buttons: {
	            "Delete": function(){
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
	            "Cancel": function(){
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
	        },
	        beforeClose: function(){
	            $("html").css("overflow", "auto");
	        },
	        buttons: {
	            "Delete": function(e){
	                var button = $(e.currentTarget);
	                button.prop("disabled", true);
                    var xhrs = new Array();
                    var toDelete = new Array();
                    this.products.each(function(product){
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
                        this.products.each(function(product){
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
	            "Cancel": function(){
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
	                            addSuccess("<b>" + nCreated + "</b> " + productsTerm.pluralize().toLowerCase() + " were created/updated");
	                        }
	                        if(nError > 0){
	                            addInfo("<b>" + nError + "</b> " + productsTerm.pluralize().toLowerCase() + " were ignored (probably duplicates)");
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
	                    var overwrite = $("input[name=overwrite]:checked", this.doiDialog).val();
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
                                    addSuccess("<b>" + nCreated + "</b> " + productsTerm.pluralize().toLowerCase() + " were created/updated");
                                }
                                if(nError > 0){
                                    addInfo("<b>" + nError + "</b> " + productsTerm.pluralize().toLowerCase() + " were ignored (probably duplicates)");
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
	    $(window).resize(function(){
	        this.editDialog.dialog({height: $(window).height()*0.75});
	    }.bind(this));
        return this.$el;
    }

});
