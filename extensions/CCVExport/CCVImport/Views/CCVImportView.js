CCVImportView = Backbone.View.extend({

    template: _.template($("#ccv_import_template").html()),

    initialize: function(options){
        this.people = new People();
        this.people.roles = ['PNI','CNI','AR'];
        this.listenTo(this.people, "sync", $.proxy(function(){
            this.render();
        }, this));
        this.people.fetch();
        this.model.on("change:person", this.render);
    },
    
    changeSelection: function(){
        var person = this.people.findWhere({id: this.$("#member-select").val()});;
        this.model.set('person', person);
    },
    
    upload: function(){
        var button = $("#upload");
        button.prop("disabled", true);
        this.$(".throbber").show();
        ccvUploaded = $.proxy(function(response, error){
            // Purposefully global so that iframe can access
            if(error == undefined || error == ""){
                clearAllMessages();
                var success = new Array();
                var nCreated = response.created.length;
                var nError = response.error.length;
                var nHQP = (response.supervises != undefined) ? response.supervises.length : 0;
                var nFunding = (response.funding != undefined) ? response.funding.length : 0;
                if(nCreated > 0){
                    success.push("<b>" + nCreated + "</b> products were created");
                }
                if(nHQP > 0){
                    success.push("<b>" + nHQP + "</b> HQP were created/updated");
                }
                if(nFunding > 0){
                    success.push("<b>" + nFunding + "</b> Funding Contributions were created/updated");
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
                    addWarning("Nothing was imported");
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
        }, this);
        var form = this.$("form");
        form.submit();
    },
    
    events: {
        "change #member-select": "changeSelection",
        "click #upload": "upload"
    },
    
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        this.$("#member-select").chosen();
        return this.$el;
    }

});
