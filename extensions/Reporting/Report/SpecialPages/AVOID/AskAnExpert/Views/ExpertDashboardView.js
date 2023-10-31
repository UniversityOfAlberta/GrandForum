ExpertDashboardView = Backbone.View.extend({
    editDialog: null,
    registerDialog:null,
    detailsDialog:null,
    template: _.template($('#expert_dashboard_template').html()),
    initialize: function () {
        this.model.bind('sync', this.render);//change to on
    },

    events: {
        "click #editeventbtn": "openEdit",
        "click .registerbtn": "openRegister",
        "click #detailsbtn": "openDetails",
    },

    openEdit: function () {
        var view = new ExpertEditView({ el: this.editDialog, model: this.model, isDialog: true , parent_location: location});
        this.editDialog.view = view;
        this.editDialog.dialog({
            height: $(window).height() * 0.60,
            width: 500,
            title: "<en>Edit Event</en><fr>Suggérer un événement</fr>",
        });
        this.editDialog.dialog('open');
        view.render();
    },

    openRegister: function(ev){
        var cat = $(ev.currentTarget).data('cat');
        var question = false;
        var heightmultiplier = 0.60;
        var title = "Registration";
        var width = 550;
        if(cat == "question"){
            question = true;
                heightmultiplier = 0.38;
            width= 360;
            title = "Ask a Question";
                $('.my-dialog .ui-button-text:contains(Submit)').text('Ask Question');
                $('.my-dialog .ui-button-text:contains(Register)').text('Ask Question');

            }
        if(cat== "register"){
                $('.my-dialog .ui-button-text:contains(Ask Question)').text('Register');
            $('.my-dialog .ui-button-text:contains(Submit)').text('Register');
        }
        var view = new EventRegisterView({ el: this.registerDialog, model: this.model, isDialog: true, isQuestion: question});
        this.registerDialog.view = view;
        this.registerDialog.dialog({
            height: $(window).height() * heightmultiplier,
            width: width,
            title: title,
        });
        this.registerDialog.dialog('open');
        view.render();
    },

    openDetails: function () {
        var view = new ExpertDetailsView({ el: this.detailsDialog, model: this.model, isDialog: true , parent_location: location});
        this.detailsDialog.view = view;
        this.detailsDialog.dialog({
            height: $(window).height() * 0.60,
            width: 500,
            title: "Details of Event",
        });
        this.detailsDialog.dialog('open');
        view.render();
    },

    render: function () {
        this.$el.empty();
        var data = this.model.toJSON();
        if(data["date_of_event"] != null){
            //split time and date TODO: do this in class function instead
            var origDate = new Date(data["date_of_event"].replace(/-/g, "/"));
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
        if(data["end_of_event"] != null){
            //split time and date TODO: do this in class function instead
            var origDate = new Date(data["end_of_event"].replace(/-/g, "/"));
            var split = data["end_of_event"].split(" ");
            var parts = split[0].split('-');
            var date = new Date(parts[0], parts[1] - 1, parts[2]);
            var timesplit = split[1].split(":");
            var time = timesplit[0] + ":" + timesplit[1];
            var datestring = date.toDateString();
            data["end_date"] = datestring;
            data["end_time"] = origDate.toLocaleString('en-US', { hour: 'numeric', hour12: true, minute: 'numeric' });
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
            },
            dialogClass: 'my-dialog'
        });

        this.detailsDialog = this.$("#detailsDialog").dialog({
            autoOpen: false,
            modal: true,
            show: 'fade',
            resizable: false,
            draggable: false,
            open: function () {
                $("html").css("overflow", "hidden");
            },
            beforeClose: function () {
                this.detailsDialog.view.stopListening();
                this.detailsDialog.view.undelegateEvents();
                this.detailsDialog.view.$el.empty();
                $("html").css("overflow", "auto");
            }.bind(this),
        });

        return this.$el;
    }

});
