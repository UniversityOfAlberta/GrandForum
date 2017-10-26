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
        "change #coursemember-select": "changeSelection",
        "click #editGsmsUpload": "upload"
    },

    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        this.$("#coursemember-select").chosen();
        return this.$el;
    }

});
