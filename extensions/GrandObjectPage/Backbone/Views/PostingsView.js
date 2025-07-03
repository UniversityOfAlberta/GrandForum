PostingsView = Backbone.View.extend({

    initialize: function(){
        this.model.fetch();
        main.set('title', 'Postings');
        this.listenTo(this.model, "sync", this.render);
        this.listenTo(this.model, "remove", this.render);
    },
       
    events: {
        "click #add": "addPostings",
        "click .delete-icon": "delete",
    },

    showAllRows: function() {
        table = document.getElementById("postings");
        tr = table.getElementsByTagName("tr");

        for (i = 0; i < tr.length; i++) {
            tr[i].style.display = "";
        }
    },

    delete: function(e) {
        if (confirm("Are you sure you want to delete this posting?")) {
            this.model.get(e.target.id).destroy({
                success: function(model, response) {
                    if(response.deleted == true){
                        model.set(response);
                        clearSuccess();
                        clearError();
                        addSuccess('The Posting <i>' + response.title + '</i> was deleted sucessfully');
                    }
                    else{
                        clearSuccess();
                        clearError();
                        addError('The Posting <i>' + response.title + '</i> was not deleted sucessfully');
                    }
                },
                error: function(model, response) {
                    clearSuccess();
                    clearError();
                    addError('The Posting <i>' + response.title + '</i> was not deleted sucessfully');
                }
            });
        }
    },
    
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        this.$("table#postings").DataTable({
            "autoWidth": true,
            "order": [[ 0, "desc" ]],
            'dom': 'Blfrtip',
            'buttons': [
                'excel'
            ]
        });
        return this.$el;
    }

});
