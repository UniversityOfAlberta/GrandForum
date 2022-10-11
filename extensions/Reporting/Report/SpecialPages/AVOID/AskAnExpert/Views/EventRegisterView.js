EventRegisterView = Backbone.View.extend({
    isDialog: false,
    isQuestion: false,

    initialize: function(options){
        this.parent = this;
        this.listenTo(this.model, "sync", this.render);

        if(options.isDialog != undefined){
            this.isDialog = options.isDialog;
        }
	if(options.isQuestion != undefined){
	    this.isQuestion = options.isQuestion;
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
	        if(this.isQuestion){
		    dataToSend.question = form.find('#question').val();
		    dataToSend.topic = "Registration & Question";
		}
	    	else{
		    dataToSend.question = "No Question";
		}
                $.post(wgServer + wgScriptPath + '/index.php?action=registerExpertEventAction', dataToSend, function(response){
                    $(this).dialog('close');
                    clearSuccess();
                    addSuccess('Thank you for submitting a question to our expert of the month. It is possible that our expert may not be able to answer all questions, but we will be sure to get to the most common ones.', true);
                }.bind(this));
    },
    
    cancel: function(){
    },

    
    render: function(){
	var data = this.model.toJSON();
        var date = "";
        var time = "";
        data["date"] = "";
        data["time"] = "";
        //format date
        if(data["date_of_event"] != null){
                var split = data["date_of_event"].split(" ");
                date = split[0];
                time = split[1];
                data["date"] = date;
                data["time"] = time;
        }

        this.$el.html(this.template(data));
        return this.$el;
    }

});
