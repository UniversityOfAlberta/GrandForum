CRMContactsTableView = Backbone.View.extend({

    table: null,
    editDialog: null,

    initialize: function(){
        this.model.fetch();
        this.listenTo(this.model, "sync", this.render);
        this.template = _.template($('#crm_contacts_table_template').html());
        main.set('title', 'Manage CRM');
        this.listenTo(this.model, "remove", this.render);
    },
       
    events: {
        "click #addContact": "addContact",
        "click .edit-icon": "editContact"
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
            'aLengthMenu': [[-1], ['All']]
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
	    
        return this.$el;
    }

});
