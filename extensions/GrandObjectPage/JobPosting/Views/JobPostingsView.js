JobPostingsView = Backbone.View.extend({

    initialize: function(){
        this.model.fetch();
        this.listenTo(this.model, "sync", this.render);
        this.template = _.template($('#jobpostings_template').html());
        main.set('title', 'Job Postings');
        this.listenTo(this.model, "remove", this.render);
    },
       
    events: {
        "click #add": "addJobPostings",
        "click .delete-icon": "delete",
    },

    showAllRows: function() {
        table = document.getElementById("jobpostings");
        tr = table.getElementsByTagName("tr");

        for (i = 0; i < tr.length; i++) {
            tr[i].style.display = "";
        }
    },

    delete: function(e) {
        if (confirm("Are you sure you want to delete this job posting?")) {
            this.model.get(e.target.id).destroy({
                success: function(model, response) {
                    if(response.deleted == true){
                        model.set(response);
                        clearSuccess();
                        clearError();
                        addSuccess('The Job Posting <i>' + response.title + '</i> was deleted sucessfully');
                    }
                    else{
                        clearSuccess();
                        clearError();
                        addError('The Job Posting <i>' + response.title + '</i> was not deleted sucessfully');
                    }
                },
                error: function(model, response) {
                    clearSuccess();
                    clearError();
                    addError('The Job Posting <i>' + response.title + '</i> was not deleted sucessfully');
                }
            });
        }
    },
    
    render: function(){
        this.model.each(function(bib){
            var editors = new Array();
            _.each(bib.get('editors'), function(editor){
                editors.push("<a style='white-space: nowrap;' href=" + editor.url + ">" + editor.fullname + "</a>");
            });
            bib.set('editorsHTML', editors.join(", "));
            var tags = new Array();
            _.each(bib.get('tags'), function(listTags){
                for (i = 0; i < listTags.length; i++) {
                    if (!_.include(tags, listTags[i])) {
                        tags.push(listTags[i]);
                    }
                }
            });
            bib.set('tagsHTML', tags.sort().join(", "));
        });
        this.$el.html(this.template(this.model.toJSON()));
        this.$("table#jobpostings").DataTable({
            "autoWidth": true,
            "order": [[ 0, "desc" ]]
        });
        return this.$el;
    }

});
