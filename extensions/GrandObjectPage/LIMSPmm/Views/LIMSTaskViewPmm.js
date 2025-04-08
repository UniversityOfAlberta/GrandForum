LIMSTaskViewPmm = Backbone.View.extend({

    tagName: "tr",

    editDialog: null,


    initialize: function(){
        this.listenTo(this.model, "sync", this.render);
        this.template = _.template($('#lims_task_template').html());
        this.editDialog = $('<div></div>');
    },

    events: {
        "click #checkStatus": "checkStatus"
    },

    checkStatus: function(){
        // Create a model for the status change dialog
        var view = new LIMSStatusCheckViewPmm({el: this.editDialog, model: this.model, isDialog: true});
        
        this.editDialog.view = view;
        $('body').append(this.editDialog);

        // Check if the dialog is already initialized
        if (this.editDialog.dialog('instance')) {
            this.editDialog.dialog('destroy');
        }
        
        $('body').append(this.editDialog);
        
        this.editDialog.dialog({
            height: $(window).height() * 0.75,
            width: 400,
            title: "Check Task Status"
        });

        // Open the dialog
        this.editDialog.dialog('open');
    },

    
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        return this.$el;
    }

});
