EditBioView = Backbone.View.extend({
    template: _.template($("#edit_bio_template").html()),

    initialize: function(options){
        this.parent = options.parent;
        this.people = new People();
        this.people.roles = [NI];
        this.parent = options.parent;
        if(this.parent.currentRoles.where({name:ADMIN}).length == 0){
            this.model.set("person", me);
        }
        this.listenTo(this.people, "sync", $.proxy(function(){
            this.render();
        }, this));
        this.people.fetch();
        this.model.on("change:person", this.render);
    },

    changeSelection: function(){
        var person = this.people.findWhere({id: this.$("#coursemember-select").val()});;
        this.model.set('person', person);
    },

    upload: function(){
        var button = $("#editGsmsUpload");
        button.prop("disabled", true);
        this.$(".throbber").show();
        ccvUploaded = $.proxy(function(response, error){
            // Purposefully global so that iframe can access
            if(error == undefined || error == ""){
                clearAllMessages();
/*                var success = new Array();
                var warning = new Array();
                var nCreated = response.created.length;
                var nError = response.error.length;
                var nFunding = (response.funding != undefined) ? response.funding.length : 0;
                var fundingFail = (response.fundingFail != undefined) ? response.fundingFail : 0;
                if(nCreated > 0){
                    success.push("<b>" + nCreated + "</b> products were created");
                }
                if(nFunding > 0){
                    success.push("<b>" + nFunding + "</b> Courses were created/updated");
                }
                if(fundingFail > 0){
                    warning.push("<b>" + fundingFail + "</b> Courses failed to import");
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
                }*/
                button.prop("disabled", false);
            }
            else{
                button.prop("disabled", false);
                clearAllMessages();
                addError(error);
            }
            this.$(".throbber").hide();
        }, this);
        var form = this.$("form");
        form.submit();
    },

    events: {
        "change #coursemember-select": "changeSelection",
        "click #editGsmsUpload": "upload"
    },

    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        this.$("#coursemember-select").chosen();
        return this.$el;
    }

});
