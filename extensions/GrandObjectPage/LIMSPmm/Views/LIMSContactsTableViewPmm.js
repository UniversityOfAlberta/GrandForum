LIMSContactsTableViewPmm = Backbone.View.extend({

    table: null,
    editDialog: null,
    deleteDialog: null,
    groupBy: 4,

    initialize: function(){
        this.model.fetch();
        this.listenTo(this.model, "sync", this.render);
        this.listenTo(this.model, "remove", function(){ _.defer(this.render); }.bind(this) );
        this.template = _.template($('#lims_contacts_table_template').html());
        main.set('title', 'Manage LIMS');
    },
       
    events: {
        "click #addContact": "addContact",
        "click .edit-icon": "editContact",
        "click .delete-icon": "deleteContact",
        "change .opportunity-status": "changeStatus",
    },
    
    addContact: function(e){
        var view = new LIMSContactEditViewPmm({el: this.editDialog, model: new LIMSContactPmm(), isDialog: true});
        view.render();
        this.editDialog.view = view;
        this.editDialog.dialog({
            height: $(window).height()*0.75, 
            title: "Add Customer/User"
        });
        this.editDialog.dialog('open');
    },
    
    editContact: function(e){
        var id = $(e.currentTarget).attr("data-id");
        var view = new LIMSContactEditViewPmm({el: this.editDialog, model: new LIMSContactPmm({id: id}), isDialog: true});
        this.editDialog.view = view;
        this.editDialog.dialog({
            height: $(window).height()*0.75, 
            title: "Edit Customer/User"
        });
        this.editDialog.dialog('open');
    },
    
    deleteContact: function(e){
        var id = $(e.currentTarget).attr("data-id");
        this.deleteDialog.model = this.model.get(id);
        this.deleteDialog.dialog('open');
    },
    
    changeStatus: function(e){
        $(e.currentTarget).prop('disabled', true);
        var id = $(e.currentTarget).attr("data-id");
        var status = $("option:selected", e.currentTarget).val();
        var model = new LIMSOpportunityPmm({id: id});
        model.fetch().then(function(){
            model.set('status', status);
            model.save().then(function(){
                this.model.fetch();
            }.bind(this));
        }.bind(this));
    },
    
    initTable: function(){
        // Initialize order/filter
        var searchStr = "";
        var order = [4, "asc"];
        if(this.table != undefined){
            order = this.table.order();
            searchStr = this.table.search();
        }
        var rowsGroup = [1,0,2,3,4,5,6,7,8];
        if(isAllowedToCreateLIMSPmmContacts){
            rowsGroup = [1,0,2,3,4,5,6,7,8];
        }
        if(this.groupBy != null){
            rowsGroup = [this.groupBy].concat(rowsGroup);
        }
        this.table = this.$("table#contacts").DataTable({
            "autoWidth": true,
            'bPaginate': false,
            'iDisplayLength': -1,
            'order': [[ 1, "asc" ]],
            'aLengthMenu': [[-1], ['All']],
            'rowsGroup': rowsGroup,
            'dom': 'Blfrtip',
            'buttons': [
                {
                    extend: 'excel',
                    text: 'Excel',
                    exportOptions: {
                        format: {
                            body: function (html, row, col, node) {
                                var html = $("<div>" + html + "</div>");
                                $("span", html).remove();
                                $("br", html).remove();
                                if($("select", html).length > 0){
                                    $(html).text($("select option:selected", html).val());
                                }
                                return $(html).text().trim().replaceAll("\n", "");
                            }
                        }
                    }
                }
            ],
        });
        table = this.table;
        this.table.order(order);
	    this.table.search(searchStr);
	    this.table.draw();
	    this.table.on('order.dt', function(e, el, ord){
            if(ord.length == 1){
                this.groupBy = ord[0].src;
                this.render();
            }
            else{
                this.groupBy = null;
            }
        }.bind(this));
        this.$('#contacts_wrapper').prepend("<div id='contacts_length' class='dataTables_length'></div>");
	    this.$("#contacts_length").empty();
	    this.$("#contacts_length").append(this.$("#addContact").detach());
	    this.$("#contacts_length").append(this.$(".dt-buttons button").detach());
	    this.$(".dt-buttons").remove();
	    
	    this.$("#addContact").css("margin-right", "5px");
    },
    
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        this.initTable();
	    this.editDialog = this.$("#editDialog").dialog({
	        autoOpen: false,
	        modal: true,
	        show: 'fade',
	        width: '1100px',
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
	        buttons: {
	            "Save": {
                    text: "Save Customer/User",
                    click: function(){
                        $(".ui-dialog-buttonset .throbber", this.editDialog.parent()).show();
                        var buttons = $(".ui-dialog-buttonset button", this.editDialog.parent());
                        $(buttons).prop('disabled', true);
                        // Save Contact
                        $.when.apply(null, this.editDialog.view.save()).done(function(){
                            // Save Opportunities
                            $.when.apply(null, this.editDialog.view.saveOpportunities()).done(function(){
                                // Save Tasks
                                $.when.apply(null, this.editDialog.view.saveTasks()).done(function(){
                                    $(buttons).prop('disabled', false);
                                    $(".ui-dialog-buttonset .throbber", this.editDialog.parent()).hide();
                                    this.editDialog.dialog("close");
                                    this.model.fetch();
                                    clearAllMessages();
                                    addSuccess("The Contact has been saved sucessfully");
                                }.bind(this));
                            }.bind(this));
                        }.bind(this)).fail(function(e){
                            $(buttons).prop('disabled', false);
                            $(".ui-dialog-buttonset .throbber", this.editDialog.parent()).hide();
                            clearAllMessages("#dialogMessages");
                            addError(e.responseText, true, "#dialogMessages");
                        }.bind(this));
                    }.bind(this)
                },
                "Cancel": function(){
	                this.editDialog.dialog('close');
	            }.bind(this)
            }
	    });
	    
	    $(".ui-dialog-buttonset", this.editDialog.parent()).prepend("<span class='throbber' style='display:none;'></span>");
	    
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
                    $("div.throbber", this.deleteDialog).show();
                    model.destroy({
                        success: function(model, response) {
                            this.deleteDialog.dialog('close');
                            clearSuccess();
                            clearError();
                            addSuccess('The contact was deleted sucessfully');
                        }.bind(this),
                        error: function(model, response) {
                            this.deleteDialog.dialog('close');
                            clearSuccess();
                            clearError();
                            addError('The contact was not deleted sucessfully');
                        }.bind(this),
                        wait: true
                    });
	            }.bind(this),
	            "Cancel": function(){
	                this.deleteDialog.dialog('close');
	            }.bind(this)
	        }
	    });
	    
        return this.$el;
    }

});
