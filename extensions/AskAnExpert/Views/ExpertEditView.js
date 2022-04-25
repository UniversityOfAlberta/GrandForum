ExpertEditView = Backbone.View.extend({
    isDialog: false,

    initialize: function(options){
        this.parent = this;
        this.listenTo(this.model, "sync", this.render);

        if(options.isDialog != undefined){
            this.isDialog = options.isDialog;
        }
        this.template = _.template($('#expert_edit_template').html());
    },
    
    events: {
        "click #saveEvent": "saveEvent",
        "click #cancel": "cancel",
    },

    validate: function(){
	var form = $('form#eventform');
	if(form.find('#name_of_expert').val() == ""){
	    return "Please enter a name for expert"
	}
	if(form.find('#expert_field').val() == ""){
            return "Please enter a field of expert"
        }

        return "";
    },
    
    saveEvent: function(){
        var validation = this.validate();
        if(validation != ""){
            clearAllMessages();
            addError(validation, true);
            return;
        }
        this.$(".throbber").show();
        this.$("#saveEvent").prop('disabled', true); //disable button
	//grab form stuff
	var form = $('form#eventform');
	var date = form.find('#date_of_event').val();
	var time = form.find('#time_of_event').val();
	var datetimestr = date+" "+time+":00";
	this.model.set({
		"name_of_expert": form.find('#name_of_expert').val(),
		"expert_field": form.find('#expert_field').val(),
		"zoomlink": form.find('#zoomlink').val(),
		"date_of_event": datetimestr,
	});
	console.log(this.model);
        this.model.save(null, {
            success: function(){
                this.$(".throbber").hide();
                this.$("#saveEvent").prop('disabled', false);
                clearAllMessages();
            }.bind(this),
            error: function(o, e){
                this.$(".throbber").hide();
                this.$("#saveEvent").prop('disabled', false);
                clearAllMessages();
                if(e.responseText != ""){
                    addError(e.responseText, true);
                }
                else{
                    addError("There was a problem saving the Event", true);
                }
            }.bind(this)
        });
    },
    
    cancel: function(){
    },

    
    render: function(){
	var data = this.model.toJSON();
	var split = data["date_of_event"].split(" ");
        var date = split[0];
        var time = split[1];
        data["date"] = date;
        data["time"] = time;

        this.$el.html(this.template(data));
        return this.$el;
    }

});
