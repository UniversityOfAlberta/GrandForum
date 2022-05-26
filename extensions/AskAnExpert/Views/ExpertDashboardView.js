ExpertDashboardView = Backbone.View.extend({
    editDialog: null,
    registerDialog:null,
    template: _.template($('#expert_dashboard_template').html()),
    initialize: function () {
        this.model.bind('sync', this.render);//change to on
    },

    events: {
        "click #editeventbtn": "openEdit",
	"click .registerbtn": "openRegister",
    },

    openEdit: function () {
        var view = new ExpertEditView({ el: this.editDialog, model: this.model, isDialog: true , parent_location: location});
        this.editDialog.view = view;
        this.editDialog.dialog({
            height: $(window).height() * 0.55,
            width: 350,
            title: "Edit Event",
        });
        this.editDialog.dialog('open');
        view.render();
    },


    openRegister: function(ev){
	var cat = $(ev.currentTarget).data('cat');
	var question = false;
	var heightmultiplier = 0.35;
	if(cat == "question"){
	    question = true;
            heightmultiplier = 0.50;
        }
        var view = new EventRegisterView({ el: this.registerDialog, model: this.model, isDialog: true, isQuestion: question});
        this.registerDialog.view = view;
        this.registerDialog.dialog({
            height: $(window).height() * heightmultiplier,
            width: 350,
            title: "Ask a Question",
        });
        this.registerDialog.dialog('open');
        view.render();
    },


    render: function () {
        this.$el.empty();
        var data = this.model.toJSON();
	if(data["date_of_event"] != null){
        //split time and date TODO: do this in class function instead
            var origDate = new Date(data["date_of_event"]);
        	var split = data["date_of_event"].split(" ");
        	var parts = split[0].split('-');
        	var date = new Date(parts[0], parts[1] - 1, parts[2]);
        	var timesplit = split[1].split(":");
        	var time = timesplit[0] + ":" + timesplit[1];
        	var datestring = date.toDateString();
        	data["date"] = datestring;
        	data["time"] = origDate.toLocaleString('en-US', { hour: 'numeric', hour12: true, minute: 'numeric' });
	}
	if(data["date_for_questions"] != null){
        //split time and date TODO: do this in class function instead
                var split = data["date_for_questions"].split(" ");
                var parts = split[0].split('-');
                var date = new Date(parts[0], parts[1] - 1, parts[2]);
                var timesplit = split[1].split(":");
                var time = timesplit[0] + ":" + timesplit[1];
                var datestring = date.toDateString();
                data["date_for_questions"] = datestring;
        }
        this.$el.html(this.template({
            output: data,
        }));


        this.editDialog = this.$("#editDialog").dialog({
            autoOpen: false,
            modal: true,
            show: 'fade',
            resizable: false,
            draggable: false,
            open: function () {
                $("html").css("overflow", "hidden");
            },
            beforeClose: function () {
                this.editDialog.view.stopListening();
                this.editDialog.view.undelegateEvents();
                this.editDialog.view.$el.empty();
                $("html").css("overflow", "auto");
            }.bind(this),
            buttons: {
                "Save": function () {
                    this.editDialog.view.saveEvent();
                    this.editDialog.dialog('close');
                }.bind(this),

                "Cancel": function () {
                    this.editDialog.dialog('close');
                }.bind(this)
            }
        });

        this.registerDialog = this.$("#registerDialog").dialog({
            autoOpen: false,
            modal: true,
            show: 'fade',
            resizable: false,
            draggable: false,
            open: function () {
                $("html").css("overflow", "hidden");
            },
            beforeClose: function () {
                this.registerDialog.view.stopListening();
                this.registerDialog.view.undelegateEvents();
                this.registerDialog.view.$el.empty();
                $("html").css("overflow", "auto");
            }.bind(this),
            buttons: {
                "Submit": function () {
                    this.registerDialog.view.registerEvent();
                    this.registerDialog.dialog('close');
		    this.window.scrollTo(0, 0);
                }.bind(this),

                "Cancel": function () {
                    this.registerDialog.dialog('close');
		    this.window.scrollTo(0, 0);
                }.bind(this)
            }
        });



        return this.$el;
    }

});
