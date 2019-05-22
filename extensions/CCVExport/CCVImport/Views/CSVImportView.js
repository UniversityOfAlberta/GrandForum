CSVImportView = Backbone.View.extend({

    template: _.template($("#csv_import_template").html()),

    initialize: function(options){
        this.parent = options.parent;
        this.people = new People();
        this.people.roles = [NI];
        if(this.parent.currentRoles.where({name:ADMIN}).length == 0){
            this.model.set("person", me);
        }
        this.listenTo(this.people, "sync", function(){
            this.render();
        }.bind(this));
        this.people.fetch();
        this.model.on("change:person", this.render);
    },
    
    changeSelection: function(){
        var person = this.people.findWhere({id: this.$("#csvmember-select").val()});;
        this.model.set('person', person);
    },
    
    upload: function(){
        var button = $("#csvupload");
        button.prop("disabled", true);
        this.$(".throbber").show();
        ccvUploaded = function(response, error){
            // Purposefully global so that iframe can access
            if(error == undefined || error == ""){
                clearAllMessages();
                var success = new Array();
                var warning = new Array();
                var nCreated = response.created.length;
                var nCourses = (response.courses != undefined) ? response.courses.length: 0;
                var nError = response.error.length;
                var nPresentations = (response.presentations != undefined) ? response.presentations.length: 0;
                var nAdditionals = (response.additionals != undefined) ? response.additionals.length: 0;
                var nAwards = (response.awards != undefined) ? response.awards.length: 0;
                var nHQP = (response.supervises != undefined) ? response.supervises.length : 0;
                var nFunding = (response.funding != undefined) ? response.funding.length : 0;
                var fundingFail = (response.fundingFail != undefined) ? response.fundingFail : 0;
                if(nCreated > 0){
                    success.push("<b>" + nCreated + "</b> products were created");
                }
                if(response.fec_info!= undefined){
                    success.push("FEC personal information was created/updated");
                }
                if(nCourses > 0){
                    success.push("<b>" + nCourses + "</b> courses were created/updated");
                }
                if(nHQP > 0){
                    success.push("<b>" + nHQP + "</b> HQP were created/updated");
                }
                if(nPresentations > 0){
                    success.push("<b>" + nPresentations + "</b> Presentations were created/updated");
                }
                if(nAwards > 0){
                    success.push("<b>" + nAwards + "</b> Awards were created/updated");
                }
                if(nAdditionals > 0){
                    success.push("<b>" + nAdditionals + "</b> Additional Information was created/updated");
                }
                if(nFunding > 0){
                    success.push("<b>" + nFunding + "</b> Funding Contributions were created/updated");
                }
                if(fundingFail > 0){
                    warning.push("<b>" + fundingFail + "</b> Funding Contributions failed to import");
                }
                if(response.info != undefined){
                    success.push("Personal Information was updated");
                }
                if(response.employment != undefined){
                    success.push("Employment Information was updated");
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
        "change #csvmember-select": "changeSelection",
        "click #csvupload": "upload"
    },
    
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        this.$("#csvmember-select").chosen();
        return this.$el;
    }

});
