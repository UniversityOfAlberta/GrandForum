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
        ccvUploaded = $.proxy(function(response, error){
            // Purposefully global so that iframe can access
            if(error == undefined || error == ""){
                clearAllMessages();
                var nCreated = response.created.length;
                var nError = response.error.length;
                var nHQP = response.supervises.length;
                if(nCreated > 0){
                    addSuccess("<b>" + nCreated + "</b> products were created");
                }
                if(response.supervises.length > 0){
                    addSuccess("<b>" + nHQP + "</b> HQP were created/updated");
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
