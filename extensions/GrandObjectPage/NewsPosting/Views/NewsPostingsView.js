NewsPostingsView = Backbone.View.extend({

    initialize: function(){
        this.model.fetch();
        this.template = _.template($('#newspostings_template').html());
        main.set('title', 'News Postings');
        this.listenTo(this.model, "sync", this.render);
        this.listenTo(this.model, "remove", this.render);
    },
       
    events: {
        "click #add": "addNewsPostings",
        "click .delete-icon": "delete",
    },

    showAllRows: function() {
        table = document.getElementById("newspostings");
        tr = table.getElementsByTagName("tr");

        for (i = 0; i < tr.length; i++) {
            tr[i].style.display = "";
        }
    },

    delete: function(e) {
        if (confirm("Are you sure you want to delete this news posting?")) {
            this.model.get(e.target.id).destroy({
                success: function(model, response) {
                    if(response.deleted == true){
                        model.set(response);
                        clearSuccess();
                        clearError();
                        addSuccess('The News Posting <i>' + response.title + '</i> was deleted sucessfully');
                    }
                    else{
                        clearSuccess();
                        clearError();
                        addError('The News Posting <i>' + response.title + '</i> was not deleted sucessfully');
                    }
                },
                error: function(model, response) {
                    clearSuccess();
                    clearError();
                    addError('The News Posting <i>' + response.title + '</i> was not deleted sucessfully');
                }
            });
        }
    },
    
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        this.$("table#newspostings").DataTable({
            "autoWidth": true,
            "order": [[ 0, "desc" ]]
        });
        return this.$el;
    }

});
