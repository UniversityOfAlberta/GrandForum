ElitePostingsAdminView = PostingsView.extend({

    template: _.template($('#elitepostings_admin_template').html()),
    postingDialog: null,
    acceptDialog: null,
    moreDialog: null,
    rejectDialog: null,
    
    initialize: function(){
        this.model.fetch();
        main.set('title', 'ELITE Admin Panel');
        this.listenTo(this.model, "sync", this.render);
    },
    
    openDialog: function(el){
        var id = $(el.target).attr('data-id');
        var model = new ElitePosting({id: id});
        var view = new ElitePostingView({el: this.postingDialog, model: model, isDialog: true});
        this.postingDialog.view = view;
        this.postingDialog.dialog({
            height: $(window).height()*0.75, 
            width: 800
        });
        this.postingDialog.dialog('open');
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
        "click .postingLink": "openDialog",
        "click .accept": "openAcceptDialog",
        "click .more": "openMoreDialog",
        "click .reject": "openRejectDialog",
    },
    
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        this.$("table#postings").DataTable({
            "autoWidth": true,
            "order": [[ 0, "desc" ]]
        });
        this.postingDialog = this.$("#postingDialog").dialog({
            autoOpen: false,
            modal: true,
            show: 'fade',
            resizable: false,
            draggable: false,
            beforeClose: function(){
                this.postingDialog.view.stopListening();
                this.postingDialog.view.undelegateEvents();
                this.postingDialog.view.$el.empty();
            }.bind(this)
        });
        this.acceptDialog = this.$("#acceptDialog").dialog({
            autoOpen: false,
            modal: true,
            show: 'fade',
            resizable: false,
            draggable: false,
            buttons: {
                "Accept": function(){
                    this.acceptDialog.model.set('visibility', 'Accepted');
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
                    this.moreDialog.model.set('comments', $("#moreComments").val());
                    this.moreDialog.model.set('visibility', 'Requested More Info');
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
                    this.rejectDialog.model.set('visibility', 'Rejected');
                    this.rejectDialog.model.save();
                    this.rejectDialog.dialog('close'); 
                }.bind(this),
                "Cancel": function(){
                    this.rejectDialog.dialog('close');
                }.bind(this)
            }
        });
        $(window).resize(function(){
            this.postingDialog.dialog({height: $(window).height()*0.75});
        }.bind(this));
        return this.$el;
    }

});
