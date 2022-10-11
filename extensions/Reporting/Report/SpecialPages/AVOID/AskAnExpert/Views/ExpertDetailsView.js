ExpertDetailsView = Backbone.View.extend({
    isDialog: false,
    parent_loaction:null,

    initialize: function(options){
	this.parent_location = options.parent_location;
        this.listenTo(this.model, "sync", this.render);

        if(options.isDialog != undefined){
            this.isDialog = options.isDialog;
        }
        this.template = _.template($('#expert_details_template').html());
    },
    
    events: {
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
	//format questiondate
	data["date_for_questionsstr"] = "";
	if(data["date_for_questions"] != null){
        	var split2 = data["date_for_questions"].split(" ");
        	var date2 = split2[0];
        	data["date_for_questionsstr"] = date2;
        }

        this.$el.html(this.template(data));
        return this.$el;
    }

});
