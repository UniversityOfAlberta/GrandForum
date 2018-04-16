CollaborationsView = Backbone.View.extend({
    selectedTags: null,

    initialize: function(){
        this.selectedTags = new Array();
        this.model.fetch();
        this.listenTo(this.model, "sync", this.render);
        this.template = _.template($('#collaborations_template').html());
        main.set('title', 'Collaborations');
        this.listenTo(this.model, "remove", this.render);
    },
       
    events: {
        "click #add": "addCollaboration",
        "click .delete-icon": "delete",
    },

    showAllRows: function() {
        table = document.getElementById("collaborations");
        tr = table.getElementsByTagName("tr");

        for (i = 0; i < tr.length; i++) {
            tr[i].style.display = "";
        }
    },

    delete: function(e) {
        if (confirm("Are you sure you want to delete this collaboration?")) {
            this.model.get(e.target.id).destroy({success: $.proxy(function(model, response) {
                if (response.id != null) {
                    this.model.add(model);
                    clearAllMessages();
                    addError(model.getType() + " deletion failed");
                } else {
                    clearAllMessages();
                    addSuccess(model.getType() + " deleted");
                }
            }, this), error: function() {
                clearAllMessages();
                addError(model.getType() + " deletion failed");
            }, wait: true});
        }
    },
    
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        this.$("table#collaborations").DataTable({
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
