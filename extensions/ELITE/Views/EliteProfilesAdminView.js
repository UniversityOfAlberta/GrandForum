EliteProfilesAdminView = Backbone.View.extend({

    template: _.template($('#eliteprofiles_admin_template').html()),
    acceptDialog: null,
    moreDialog: null,
    rejectDialog: null,
    
    initialize: function(){
        this.model.fetch();
        this.listenTo(this.model, "sync", this.render);
    },
    
    openAcceptDialog: function(el){
        var id = $(el.target).attr('data-id');
        this.acceptDialog.dialog('open');
        this.acceptDialog.model = this.model.get(id);
    },
    
    openMoreDialog: function(el){
        var id = $(el.target).attr('data-id');
        this.moreDialog.dialog('open');
        this.moreDialog.model = this.model.get(id);
    },
    
    openRejectDialog: function(el){
        var id = $(el.target).attr('data-id');
        this.rejectDialog.dialog('open');
        this.rejectDialog.model = this.model.get(id);
    },
    
    events: {
        "click .accept": "openAcceptDialog",
        "click .more": "openMoreDialog",
        "click .reject": "openRejectDialog",
    },
    
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        this.$("table#profiles").DataTable({
            "autoWidth": true,
            "order": [[ 0, "desc" ]]
        });
        this.acceptDialog = this.$("#acceptDialog").dialog({
            autoOpen: false,
            modal: true,
            show: 'fade',
            resizable: false,
            draggable: false,
            buttons: {
                "Accept": function(){
                    this.acceptDialog.model.set('status', 'Accepted');
                    this.acceptDialog.model.save();
                    this.acceptDialog.dialog('close'); 
                }.bind(this),
                "Cancel": function(){
                    this.acceptDialog.dialog('close');
                }.bind(this)
            }
        });
        this.moreDialog = this.$("#moreDialog").dialog({
            autoOpen: false,
            modal: true,
            show: 'fade',
            resizable: false,
            draggable: false,
            width: 'auto',
            buttons: {
                "Submit": function(){
                    // Need to set comments aswell
                    this.moreDialog.model.set('comments', $("#moreComments", this.moreDialog).val());
                    this.moreDialog.model.set('status', 'Requested More Info');
                    this.moreDialog.model.save();
                    this.moreDialog.dialog('close'); 
                }.bind(this),
                "Cancel": function(){
                    this.moreDialog.dialog('close');
                }.bind(this)
            }
        });
        this.rejectDialog = this.$("#rejectDialog").dialog({
            autoOpen: false,
            modal: true,
            show: 'fade',
            resizable: false,
            draggable: false,
            buttons: {
                "Reject": function(){
                    this.rejectDialog.model.set('status', 'Rejected');
                    this.rejectDialog.model.save();
                    this.rejectDialog.dialog('close'); 
                }.bind(this),
                "Cancel": function(){
                    this.rejectDialog.dialog('close');
                }.bind(this)
            }
        });
        return this.$el;
    }

});
