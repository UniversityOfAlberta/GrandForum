ElitePostingsAdminView = PostingsView.extend({

    template: _.template($('#elitepostings_admin_template').html()),
    postingDialog: null,
    
    initialize: function(){
        this.model.fetch();
        main.set('title', 'Project Proposals Admin Panel');
        this.listenTo(this.model, "sync", this.render);
    },
    
    openDialog: function(el){
        var id = $(el.target).attr('data-id');
        var model = new ElitePosting({id: id});
        var view = new ElitePostingView({el: this.postingDialog, model: model, isDialog: true});
        this.postingDialog.view = view;
        this.postingDialog.dialog({
            height: $(window).height()*0.75, 
            width: 800,
            title: "Project Proposal"
        });
        this.postingDialog.dialog('open');
    },
    
    events: {
        "click .postingLink": "openDialog"
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
	        open: function(){
	            $("html").css("overflow", "hidden");
	        },
	        beforeClose: function(){
	            this.postingDialog.view.stopListening();
	            this.postingDialog.view.undelegateEvents();
	            this.postingDialog.view.$el.empty();
	            $("html").css("overflow", "auto");
	        }.bind(this)
	    });
	    $(window).resize(function(){
	        this.postingDialog.dialog({height: $(window).height()*0.75});
	    }.bind(this));
        return this.$el;
    }

});
