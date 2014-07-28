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

    initialize: function(){
        this.allProjects = new Projects();
        this.allProjects.fetch();
        this.template = _.template($('#manage_products_template').html());
        me.getProjects();
        this.listenTo(this.model, "sync", function(){
            this.products = this.model.getAll();
            this.listenTo(this.products, "add", this.addRows);
            this.listenTo(this.products, "remove", this.addRows);
            me.projects.ready().then($.proxy(function(){
                this.projects = me.projects.getCurrent();
                this.model.ready().then($.proxy(function(){
                    this.allProjects.ready().then($.proxy(function(){
                        this.otherProjects = this.allProjects.getCurrent();
                        this.oldProjects = this.allProjects.getOld();
                        this.otherProjects.remove(this.projects.models);
                        this.oldProjects.remove(this.projects.models);
                        me.projects.ready().then($.proxy(function(){
                            this.render();
                        }, this));
                    }, this));
                }, this));
            }, this));
        }, this);
    },
    
    addProduct: function(){
        var model = new Product({authors: [me.toJSON()]});
        var view = new ProductEditView({el: this.editDialog, model: model, isDialog: true});
        this.editDialog.view = view;
        this.editDialog.dialog({
            height: $(window).height()*0.75, 
            width: 800,
            title: "Create Product"
        });
        this.editDialog.dialog('open');
    },
    
    uploadCCV: function(){
        this.ccvDialog.dialog('open');
    },
    
    importBibTeX: function(){
        this.bibtexDialog.dialog('open');
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
                return "You have unsaved Products";
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
        if(sum == 0){
            this.$("#deletePrivate").prop("disabled", true);
        }
        else{
            this.$("#deletePrivate").prop("disabled", false);
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
        _.each(this.subViews, $.proxy(function(view){
            var m = view.model;
            if(this.products.where({id: m.get('id')}).length == 0){
                this.subViews = _.without(this.subViews, view);
                view.remove();
            }
        }, this));
        // Then add new ones
        var models = _.pluck(_.pluck(this.subViews, 'model'), 'id');
        this.products.each($.proxy(function(p, i){
            if(!_.contains(models, p.id)){
                // Product isn't in the table yet
                this.listenTo(p, "dirty", this.productChanged);
                if(p.dirty == undefined){
                    p.dirty = false;
                }
                var row = new ManageProductsViewRow({model: p, parent: this});
                this.subViews.push(row);
                this.$("#productRows").append(row.$el);
            }
        }, this));
        _.each(this.subViews, function(row){
            row.render();
        });
        var end = new Date();
        this.createDataTable(order, searchStr);
        this.productChanged();
    },
    
    cacheRows: function(){
        if(this.table != null){
            var rows = this.table.rows().indexes();
            var table = this.table;
            rows.each($.proxy(function(i, val){
                this.subViews[i].row = this.table.row(i);
            }, this));
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
	    this.table.draw();
	    this.table.order(order);
	    this.table.search(searchStr);
	    this.table.draw();
	    this.$('#listTable_wrapper').prepend("<div id='listTable_length' class='dataTables_length'></div>");
	    this.$("#listTable_length").empty();
	    this.$("#listTable_length").append('<button id="saveProducts">Save All <span id="saveN">(0)</span></button>');
	    this.$("#listTable_length").append('<button id="deletePrivate">Delete All Private <span id="privateN">(0)</span></button>');
	    this.$("#listTable_length").append('<span style="display:none;" class="throbber"></span>');
    },
    
    toggleSelect: function(e){
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
        this.productChanged();
    },
    
    deletePrivate: function(){
        this.deletePrivateDialog.dialog('open');
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
                // Save all Dirty Products
                xhrs.push(product.save({}, {
                    success: function(){
                        // Save was successful, mark it as 'clean'
                        product.dirty = false;
                    }
                }));
            }
        });
        $.when.apply(null, xhrs).done($.proxy(function(){
            // Success
            clearAllMessages();
            addSuccess("All products have been successfully saved");
            this.$("#saveProducts").prop('disabled', false);
            this.$(".throbber").hide();
            this.productChanged();
        }, this)).fail($.proxy(function(e){
            // Failure
            clearAllMessages();
            var list = new Array();
            list.push("There was a problem saving the following products:<ul>");
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
        }, this));
    },
    
    events: {
        "click .selectAll": "toggleSelect",
        "click #saveProducts": "saveProducts",
        "click #deletePrivate": "deletePrivate",
        "click #addProductButton": "addProduct",
        "click #uploadCCVButton": "uploadCCV",
        "click #importBibTexButton": "importBibTeX"
    },
    
    render: function(){
        this.$el.empty();
        $(document).click($.proxy(function(e){
            var popup = $("div.popupBox:visible").not(":animated").first();
            if(popup.length > 0 && !$.contains(popup[0], e.target)){
                _.each(this.subViews, function(view){
                    if(view.$("div.popupBox").is(":visible")){
                        // Need to defer the event so that unchecking a project is not in conflict
                        _.defer(function(){
                            view.model.trigger("change");
                        });
                    }
                });
            }
        }, this));
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
	        beforeClose: $.proxy(function(){
	            this.editDialog.view.stopListening();
	            this.editDialog.view.undelegateEvents();
	            $("html").css("overflow", "auto");
	        }, this),
	        buttons: {
                "Save Product": $.proxy(function(){
                    var validation = this.editDialog.view.validate();
                    if(validation != ""){
                        clearAllMessages("#dialogMessages");
                        addError(validation, true, "#dialogMessages");
                        return "";
                    }
                    this.editDialog.view.model.save(null, {
                        success: $.proxy(function(){
                            clearAllMessages();
                            this.editDialog.view.model.dirty = false;
                            this.editDialog.dialog("close");
                            addSuccess("The Product has been saved sucessfully");
                            if(this.products.indexOf(this.editDialog.view.model) == -1){
                                this.products.add(this.editDialog.view.model);
                            }
                        }, this),
                        error: $.proxy(function(){
                            clearAllMessages("#dialogMessages");
                            addError("There was an error saving Product", true, "#dialogMessages");
                        }, this)
                    });
                }, this)
            }
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
	            "Delete": $.proxy(function(){
	                var model = this.deleteDialog.model;
	                if(model.get('deleted') != true){
                        model.destroy({
                            success: $.proxy(function(model, response) {
                                this.deleteDialog.dialog('close');
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
                            }, this),
                            error: $.proxy(function(model, response) {
                                this.deleteDialog.dialog('close');
                                clearSuccess();
                                clearError();
                                addError('The ' + response.category + ' <i>' + response.title + '</i> was not deleted sucessfully');
                            }, this)
                        });
                    }
                    else{
                        this.deleteDialog.dialog('close');
                        clearAllMessages();
                        addError('This ' + model.get('category') + ' is already deleted');
                    }
	            }, this),
	            "Cancel": $.proxy(function(){
	                this.deleteDialog.dialog('close');
	            }, this)
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
	            "Delete": $.proxy(function(e){
	                var button = $(e.currentTarget);
	                button.prop("disabled", true);
                    var xhrs = new Array();
                    var toDelete = new Array();
                    this.products.each(function(product){
                        if(product.get('access_id') > 0){
                            toDelete.push(product);
                        }
                    });
                    _.each(toDelete, function(product){
                        xhrs.push(product.destroy({silent: true}));
                    });
                    $.when.apply(null, xhrs).done($.proxy(function(){
                        // Success
                        clearAllMessages();
                        addSuccess("All private products have been successfully deleted");
                        this.addRows();
                        button.prop("disabled", false);
                        this.deletePrivateDialog.dialog('close');
                    }, this)).fail($.proxy(function(e){
                        // Failure
                        clearAllMessages();
                        var list = new Array();
                        list.push("There was a problem deleting the following products:<ul>");
                        this.products.each(function(product){
                            if(product.get('access_id') > 0){
                                list.push("<li>" + product.get('title') + "</li>");
                            }
                        });
                        list.push("</ul>");
                        addError(list.join(''));
                        this.addRows();
                        button.prop("disabled", false);
                        this.deletePrivateDialog.dialog('close');
                    }, this));
	            }, this),
	            "Cancel": $.proxy(function(){
	                this.deletePrivateDialog.dialog('close');
	            }, this)
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
	            "Upload": $.proxy(function(e){
	                var button = $(e.currentTarget);
	                button.prop("disabled", true);
	                ccvUploaded = $.proxy(function(response, error){
	                    if(error == undefined || error == ""){
	                        // Purposefully global so that iframe can access
	                        this.products.add(response, {silent: true});
	                        this.addRows();
	                        clearAllMessages();
	                        var titles = _.map(_.pluck(response, 'title'), function(t){ return "<li>" + t + "</li>"; });
	                        addSuccess("The following Products were added: <ul>" + titles.join("\n") + "</ul>");
	                        button.prop("disabled", false);
	                        this.ccvDialog.dialog('close');
	                    }
	                    else{
	                        button.prop("disabled", false);
	                        clearAllMessages();
	                        addError(error);
	                        this.ccvDialog.dialog('close');
	                    }
	                }, this);
	                var form = $("form", this.ccvDialog);
	                form.submit();
	            }, this),
	            "Cancel": $.proxy(function(){
	                this.ccvDialog.dialog('close');
	            }, this)
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
	            "Import": $.proxy(function(){
	                
	                this.bibtexDialog.dialog('close');
	            }, this),
	            "Cancel": $.proxy(function(){
	                this.bibtexDialog.dialog('close');
	            }, this)
	        }
	    });
	    $(window).resize($.proxy(function(){
	        this.editDialog.dialog({height: $(window).height()*0.75});
	    }, this));
        return this.$el;
    }

});
