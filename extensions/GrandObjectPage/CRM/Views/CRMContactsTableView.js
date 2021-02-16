CRMContactsTableView = Backbone.View.extend({

    table: null,
    editDialog: null,
    deleteDialog: null,

    initialize: function(){
        this.model.fetch();
        this.listenTo(this.model, "sync", this.render);
        this.listenTo(this.model, "remove", function(){ _.defer(this.render); }.bind(this) );
        this.template = _.template($('#crm_contacts_table_template').html());
        main.set('title', 'Manage CRM');
    },
       
    events: {
        "click #addContact": "addContact",
        "click .edit-icon": "editContact",
        "click .delete-icon": "deleteContact",
    },
    
    addContact: function(e){
        var view = new CRMContactEditView({el: this.editDialog, model: new CRMContact(), isDialog: true});
        view.render();
        this.editDialog.view = view;
        this.editDialog.dialog({
            height: $(window).height()*0.75, 
            width: 800,
            title: "Add Contact"
        });
        this.editDialog.dialog('open');
    },
    
    editContact: function(e){
        var id = $(e.currentTarget).attr("data-id");
        var view = new CRMContactEditView({el: this.editDialog, model: new CRMContact({id: id}), isDialog: true});
        this.editDialog.view = view;
        this.editDialog.dialog({
            height: $(window).height()*0.75, 
            width: 800,
            title: "Edit Contact"
        });
        this.editDialog.dialog('open');
    },
    
    deleteContact: function(e){
        var id = $(e.currentTarget).attr("data-id");
        this.deleteDialog.model = this.model.get(id);
        this.deleteDialog.dialog('open');
    },
    
    render: function(){
        // Initialize order/filter
        var searchStr = "";
        var order = [ 1, "asc" ];
        if(this.table != undefined){
            order = this.table.order();
            searchStr = this.table.search();
        }
        
        this.$el.html(this.template(this.model.toJSON()));
        this.table = this.$("table#contacts").DataTable({
            "autoWidth": true,
            'bPaginate': false,
            'iDisplayLength': -1,
            'order': [[ 1, "asc" ]],
            'aLengthMenu': [[-1], ['All']],
            'rowsGroup': [0,1,2,3,4,5]
        });
        this.table.order(order);
	    this.table.search(searchStr);
	    this.table.draw();
        this.$('#contacts_wrapper').prepend("<div id='contacts_length' class='dataTables_length'></div>");
	    this.$("#contacts_length").empty();
	    this.$("#contacts_length").append(this.$("#addContact").detach());
	    
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
	        buttons: [{
                text: "Save Contact",
                click: function(){
                    var buttons = $(".ui-dialog-buttonset button", this.editDialog.parent());
                    $(buttons).prop('disabled', true);
                    // Save Contact
                    $.when.apply(null, this.editDialog.view.save()).done(function(){
                        // Save Opportunities
                        $.when.apply(null, this.editDialog.view.saveOpportunities()).done(function(){
                            // Save Tasks
                            $.when.apply(null, this.editDialog.view.saveTasks()).done(function(){
                                $(buttons).prop('disabled', false);
                                this.editDialog.dialog("close");
                                this.model.fetch();
                                clearAllMessages();
                                addSuccess("The Contact has been saved sucessfully");
                            }.bind(this));
                        }.bind(this));
                    }.bind(this));
                }.bind(this)
            }]
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
