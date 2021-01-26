GsmsNotesImportView = Backbone.View.extend({
    template: _.template($("#gsms_notes_import_template").html()),

    initialize: function(options){
        this.parent = options.parent;
    },

    upload: function(){
        var button = $("#gsmsUpload");
        button.prop("disabled", true);
        this.$(".throbber").show();
        ccvUploaded = function(success, errors){
            // Purposefully global so that iframe can access
                clearAllMessages();
                if(success != ""){
                    addSuccess(success);
                }
                if(errors != ""){
                    addError(errors);
                }
                button.prop("disabled", false);
            this.$(".throbber").hide();
        }.bind(this);
        var form = this.$("form");
        form.submit();
    },

    events: {
        "click #gsmsUpload": "upload"
    },

    render: function(){
        this.$el.html(this.template());
        return this.$el;
    }

});
