StudentImportView = Backbone.View.extend({

    template: _.template($("#student_import_template").html()),

    initialize: function(options){
        this.people = new People();
        this.people.roles = [NI,HQP];
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
        ccvUploaded = $.proxy(function(success, errors){
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
