EliteAdminView = PostingsView.extend({

    template: _.template($('#elite_admin_template').html()),
    
    initialize: function(){
        main.set('title', 'ELITE Admin Panel');
        Backbone.Subviews.add(this);
    },
    
    subviewCreators: {
        "postings" : function() {
            var postings = new ElitePostings();
            return new EliteAdminPostingsView({model: postings});
        },
        "intern_profiles": function(){
            var profiles = new InternEliteProfiles();
            return new EliteAdminProfilesView({model: profiles});
        },
        "phd_profiles": function(){
            var profiles = new PhDEliteProfiles();
            return new EliteAdminProfilesView({model: profiles});
        }
    },
    
    render: function(){
        this.$el.html(this.template());
        return this.$el;
    }

});

EliteAdminPostingsView = PostingsView.extend({

    template: _.template($('#elite_admin_postings_template').html()),
    postingDialog: null,
    acceptDialog: null,
    moreDialog: null,
    rejectDialog: null,
    matchDialog: null,
    matchConfirmDialog: null,
    
    initialize: function(){
        this.model.fetch();
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
                    this.moreDialog.model.set('comments', $("#moreComments", this.moreDialog).val());
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

EliteAdminProfilesView = Backbone.View.extend({

    template: _.template($('#elite_admin_profiles_template').html()),
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
            width: 'auto',
            maxWidth: 800,
            resizable: false,
            draggable: false,
            buttons: {
                "Match": function(){
                    $("#matchConfirmDialog ul").empty();
                    if($("input[type=checkbox]:checked", this.matchDialog).length > 0){
                        $("input[type=checkbox]:checked", this.matchDialog).each(function(i, el){
                            $("#matchConfirmDialog ul").append("<li>" + $(el).parent().text() + "</li>");
                        });
                    }
                    else{
                        $("#matchConfirmDialog ul").append("<li>No Projects Selected</li>");
                    }
                    this.matchConfirmDialog.dialog('open');
                }.bind(this),
                "Cancel": function(){
                    this.matchDialog.dialog('close');
                }.bind(this)
            }
        });
        this.matchConfirmDialog = this.$("#matchConfirmDialog").dialog({
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
                    this.matchConfirmDialog.dialog('close');
                    this.matchDialog.dialog('close');
                }.bind(this),
                "Cancel": function(){
                    this.matchConfirmDialog.dialog('close');
                }.bind(this)
            }
        });
        return this.$el;
    }

});