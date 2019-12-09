LeverageView = CollaborationView.extend({

    initialize: function(){
        this.model.fetch({
            error: function(e){
                this.$el.html("This Leverage does not exist");
            }.bind(this)
        });
        this.model.bind('change', this.render, this);
        this.template = _.template($('#leverage_template').html());
    },

    delete: function(e) {
        var type = this.model.getType();
        if (confirm("Are you sure you want to delete this leverage?")) {
            this.model.destroy({success: function() {
                document.location = wgServer + wgScriptPath + "/index.php/Special:CollaborationPage#";
                _.defer(function() {
                    clearAllMessages();
                    addSuccess(type + " deleted")
                });
            }, error: function() {
                clearAllMessages();
                addError(type + " deletion failed");
            }});
        }
    }

});
