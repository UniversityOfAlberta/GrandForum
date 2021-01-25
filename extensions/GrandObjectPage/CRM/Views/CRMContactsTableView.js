CRMContactsTableView = Backbone.View.extend({

    initialize: function(){
        this.model.fetch();
        this.listenTo(this.model, "sync", this.render);
        this.template = _.template($('#crm_contacts_table_template').html());
        main.set('title', 'Manage CRM');
        this.listenTo(this.model, "remove", this.render);
    },
       
    events: {
        "click #add": "addContact"
    },
    
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        this.$("table#contacts").DataTable({
            "autoWidth": true,
            'bPaginate': false,
            'iDisplayLength': -1,
            'order': [[ 1, "asc" ]],
            'aLengthMenu': [[-1], ['All']]
        });
        this.$('#contacts_wrapper').prepend("<div id='contacts_length' class='dataTables_length'></div>");
	    this.$("#contacts_length").empty();
	    this.$("#contacts_length").append(this.$("#addContact").detach());
        return this.$el;
    }

});
