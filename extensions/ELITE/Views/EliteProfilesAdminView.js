EliteProfilesAdminView = Backbone.View.extend({

    template: _.template($('#eliteprofiles_admin_template').html()),
    table: null,
    acceptDialog: null,
    moreDialog: null,
    rejectDialog: null,
    matchDialog: null,
    
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
    
    openMatchDialog: function(el){
        var id = $(el.target).attr('data-id');
        this.matchDialog.html(this.$("#match_" + id).html());
        this.matchDialog.dialog('open');
        this.matchDialog.model = this.model.get(id);
    },
    
    events: {
        "click .accept": "openAcceptDialog",
        "click .more": "openMoreDialog",
        "click .reject": "openRejectDialog",
        "click .match": "openMatchDialog",
    },
    
    createDataTable: function(order, searchStr){
        this.table = this.$("table#profiles").DataTable({
            "autoWidth": true
        });
        this.table.order(order);
	    this.table.search(searchStr);
	    this.table.draw();
    },
    
    render: function(){
        var order = [[ 0, "desc" ]];
        var searchStr = "";
        if(this.table != undefined){
            order = this.table.order();
            searchStr = this.table.search();
            this.table.destroy();
            this.table = null;
        }
        this.$el.html(this.template(this.model.toJSON()));
        this.createDataTable(order, searchStr);
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
        this.matchDialog = this.$("#matchDialog").dialog({
            autoOpen: false,
            modal: true,
            show: 'fade',
            resizable: false,
            draggable: false,
            buttons: {
                "Match": function(){
                    var matches = [];
                    $("input[type=checkbox]:checked", this.matchDialog).each(function(i, el){
                        matches.push($(el).val());
                    });
                    this.matchDialog.model.set('matches', matches);
                    this.matchDialog.model.save();
                    this.matchDialog.dialog('close');
                }.bind(this),
                "Cancel": function(){
                    this.matchDialog.dialog('close');
                }.bind(this)
            }
        });
        return this.$el;
    }

});
