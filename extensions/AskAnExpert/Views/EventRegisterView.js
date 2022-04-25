EventRegisterView = Backbone.View.extend({
    isDialog: false,

    initialize: function(options){
        this.parent = this;
        this.listenTo(this.model, "sync", this.render);

        if(options.isDialog != undefined){
            this.isDialog = options.isDialog;
        }
        this.template = _.template($('#event_register_template').html());
    },
    
    events: {
        "click #registerEvent": "registerEvent",
        "click #cancel": "cancel",
    },

    validate: function(){
	var form = $('form#eventregisterform');
	if(form.find('#firstname').val() == ""){
	    return "Please enter a first name."
	}
	if(form.find('#lastname').val() == ""){
            return "Please enter a last name."
        }

        if(form.find('#email').val() == ""){
            return "Please enter an email."
        }
        return "";
    },
    
    registerEvent: function(){
        var validation = this.validate();
        if(validation != ""){
            clearAllMessages();
            addError(validation, true);
            return;
        }
	
        this.$(".throbber").show();
        this.$("#registerEvent").prop('disabled', true); //disable button
	//grab form stuff
	var form = $('form#eventregisterform');
	var dataToSend = {};

        dataToSend.topic = "Registration";
                dataToSend.firstname = form.find('#firstname').val();
                dataToSend.lastname = form.find('#lastname').val();
                dataToSend.email =form.find('#email').val();
                $.post(wgServer + wgScriptPath + '/index.php?action=registerExpertEventAction', dataToSend, function(response){
                    $(this).dialog('close');
                    clearSuccess();
                    addSuccess('Your message has been sent to support.');
                }.bind(this));
    },
    
    cancel: function(){
    },

    
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        return this.$el;
    }

});
