EditBioView = Backbone.View.extend({

    template: _.template($("#edit_bio_template").html()),

    initialize: function(options){
        this.people = new People();
        this.people.roles = [NI];
        this.listenTo(this.people, "sync", function(){
            this.render();
        }.bind(this));
        this.people.fetch();
        this.model.on("change:infoSheet", this.render);
    },

    changeSelection: function(){
        var person = this.people.findWhere({id: this.$("#member-select2").val()});;
        this.model.set('person', person);

	var info_sheet = new InfoSheet();
	info_sheet.attributes.id = person.get('id');
	info_sheet.fetch();
	this.model.set('infoSheet', info_sheet);
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
        "change #member-select2": "changeSelection",
        "click #upload": "upload"
    },

    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        this.$("#member-select2").chosen();
        return this.$el;
    }

});
