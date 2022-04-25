ExpertDashboardView = Backbone.View.extend({
    editDialog: null,

    template: _.template($('#expert_dashboard_template').html()),
    initialize: function () {
        this.model.bind('sync', this.render);//change to on
    },

    events: {
        "click #editeventbtn": "openEdit",
    },

    openEdit: function () {
        var view = new ExpertEditView({ el: this.editDialog, model: this.model, isDialog: true });
        this.editDialog.view = view;
        this.editDialog.dialog({
            height: $(window).height() * 0.45,
            width: 350,
            title: "Edit Event",
        });
        this.editDialog.dialog('open');
        view.render();
    },





    render: function () {
        this.$el.empty();
        var data = this.model.toJSON();
        //split time and date TODO: do this in class function instead
        var split = data["date_of_event"].split(" ");
        var parts = split[0].split('-');
        var date = new Date(parts[0], parts[1] - 1, parts[2]);
        var timesplit = split[1].split(":");
        var time = timesplit[0] + ":" + timesplit[1];
        var datestring = date.toDateString();
        data["date"] = datestring;
        data["time"] = time;
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

        return this.$el;
    }

});
