ManageSOPView = Backbone.View.extend({

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

    initialize: function(){
        this.subViews = new Array();
        this.allProjects = new Projects();
        this.projects = new Projects();
        this.otherProjects = new Projects();
        this.oldProjects = new Projects();
        this.template = _.template($('#manage_sop_template').html());
        this.listenToOnce(this.model, "sync", function(){
            this.products = this.model;
            this.listenTo(this.products, "add", this.addRows);
            this.listenTo(this.products, "remove", this.addRows);
            this.render();             
        }, this);
    },
    
    addProduct: function(){
        var model = new Product({category: "SOP", access: "Manager"});
        var view = new ProductEditView({el: this.editDialog, model: model, isDialog: true});
        this.editDialog.view = view;
        this.editDialog.dialog({
            height: $(window).height()*0.75, 
            width: 800,
            title: "Create SOP"
        });
        this.editDialog.dialog('open');
    },
    
    addRows: function(){
        var searchStr = "";
        var order = [1, 'desc'];
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
        var frag = document.createDocumentFragment();
        this.products.each(function(p, i){
            if(!_.contains(models, p.id)){
                // Product isn't in the table yet
                var row = new ManageSOPViewRow({model: p, parent: this});
                this.subViews.push(row);
                frag.appendChild(row.el);
            }
        }.bind(this));
        _.each(this.subViews, function(row){
            row.render();
        });
        this.$("#productRows").append(frag);
        this.createDataTable(order, searchStr);
        this.$("#listTable").show();
        this.table.draw();
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
        var bSortable = {'bSortable': false, 'aTargets': _.range(0, this.projects.length + 2) };
        this.table = this.$('#listTable').DataTable({'bPaginate': false,
                                                     'autoWidth': false,
                                                     'preDrawCallback': function(){
                                                        return !creating;
                                                     },
                                                     'drawCallback': renderProductLinks,
                                                     'aoColumnDefs': [
                                                        bSortable
                                                     ],
	                                                 'aLengthMenu': [[-1], ['All']]});
	    creating = false;
	    this.cacheRows();
	    this.table.order(order);
	    this.table.search(searchStr);
	    this.$('#listTable_wrapper').prepend("<div id='listTable_length' class='dataTables_length'></div>");
	    this.$("#listTable_length").empty();
    },
    
    events: {
        "click #addProductButton": "addProduct"
    },
    
    render: function(){
        this.$el.empty();
        this.$el.html(this.template());
        this.addRows();
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
	                text: "Save SOP",
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
                                product.dirty = false;
                                this.editDialog.dialog("close");
                                clearAllMessages();
                                addSuccess("The SOP has been saved sucessfully");
                                if(this.products.indexOf(this.editDialog.view.model) == -1){
                                    this.products.add(this.editDialog.view.model);
                                }
                            }.bind(this),
                            error: function(o, e){
                                clearAllMessages("#dialogMessages");
                                if(e.responseText != ""){
                                    addError(e.responseText, true, "#dialogMessages");
                                }
                                else{
                                    addError("There was a problem saving the SOP", true, "#dialogMessages");
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
	    
	    $(window).resize(function(){
	        this.editDialog.dialog({height: $(window).height()*0.75});
	    }.bind(this));
        return this.$el;
    }

});
