LeveragesView = CollaborationsView.extend({

    initialize: function(){
        this.model.fetch();
        this.listenTo(this.model, "sync", this.render);
        this.template = _.template($('#leverages_template').html());
        main.set('title', 'Leverages');
        this.listenTo(this.model, "remove", this.render);
    },

    delete: function(e) {
        if (confirm("Are you sure you want to delete this leverage?")) {
            this.model.get(e.target.id).destroy({success: function(model, response) {
                if (response.id != null) {
                    this.model.add(model);
                    clearAllMessages();
                    addError(model.getType() + " deletion failed");
                } else {
                    clearAllMessages();
                    addSuccess(model.getType() + " deleted");
                }
            }.bind(this), error: function() {
                clearAllMessages();
                addError(model.getType() + " deletion failed");
            }, wait: true});
        }
    },

});
