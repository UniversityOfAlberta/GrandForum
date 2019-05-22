GradImportView = Backbone.View.extend({

    template: _.template($("#grad_import_template").html()),

    initialize: function(options){
        this.people = new People();
    },
    
    upload: function(){
        var button = $("#gdupload");
        button.prop("disabled", true);
        this.$(".throbber").show();
        ccvUploaded = function(response, error){
            // Purposefully global so that iframe can access
            if(error == undefined || error == ""){
                clearAllMessages();
                var success = new Array();
                var warning = new Array();
                var nCreated = response.created.length;
                var nError = response.error.length;
                var nHQP = (response.students != undefined) ? response.students.length : 0;
                if(nHQP > 0){
                    success.push("<b>" + nHQP + "</b> HQP were created/updated");
                }
                if(success.length > 0){
                    addSuccess(success.join("<br />"));
                }
                else if (nError == 0){
                    warning.push("Nothing was imported");
                }
                // Show errors/warnings/info
                if(warning.length > 0){
                    addWarning(warning.join("<br />"));
                }
                if(nError > 0){
                    addInfo("<b>" + nError + "</b> products were ignored (probably duplicates)");
                }
                button.prop("disabled", false);
            }
            else{
                button.prop("disabled", false);
                clearAllMessages();
                addError(error);
            }
            this.$(".throbber").hide();
        }.bind(this);
        var form = this.$("form");
        form.submit();    
    },
    
    events: {
        "click #gdupload": "upload"
    },
    
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        return this.$el;
    }

});
