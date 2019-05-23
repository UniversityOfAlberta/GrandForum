PdfConversionView = Backbone.View.extend({

    template: _.template($("#pdf_conversion_template").html()),

    initialize: function(options){
        this.people = new People();
        this.people.roles = [NI];
        this.listenTo(this.people, "sync", function(){
            this.render();
        }.bind(this));
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
        "change #member-select": "changeSelection",
        "click #upload": "upload"
    },
    
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        this.$("#member-select").chosen();
        return this.$el;
    }

});
