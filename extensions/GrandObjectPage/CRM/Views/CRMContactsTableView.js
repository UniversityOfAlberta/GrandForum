CRMContactsTableView = Backbone.View.extend({

    initialize: function(){
        this.model.fetch();
        this.listenTo(this.model, "sync", this.render);
        this.template = _.template($('#crm_contacts_table_template').html());
        main.set('title', 'Contacts');
        this.listenTo(this.model, "remove", this.render);
    },
       
    events: {
        "click #add": "addContact"
    },
    
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        this.$("table#contacts").DataTable({
            "autoWidth": true,
            'iDisplayLength': 100,
            'dom': 'Blfrtip',
            'buttons': [
                'excel', 'pdf'
            ]
        });
        return this.$el;
    }

});
