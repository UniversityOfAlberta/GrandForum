ManageStoriesView = Backbone.View.extend({

    table: null,
    stories: null,
    editDialog: null,

    initialize: function(){
        this.template = _.template($('#manage_stories_template').html());
        this.listenTo(this.model, "sync", function(){
            this.stories = this.model;
            this.listenTo(this.stories, "add", this.addRows);
            //this.listenTo(this.stories, "remove", this.addRows);
            this.render();
        }, this);
    },
    
    addRows: function(){
        if(this.table != undefined){
            order = this.table.order();
            searchStr = this.table.search();
            this.table.destroy();
            this.table = null;
        }
        this.stories.each($.proxy(function(p, i){
            var row = new ManageStoriesRowView({model: p, parent: this});
            this.$("#personRows").append(row.$el);
            row.render();

        }, this));
        this.createDataTable();
    },
    
    createDataTable: function(order, searchStr){
        this.table = this.$('#listTable').DataTable({'bPaginate': false,
                                                     'autoWidth': false,
	                                                 'aLengthMenu': [[-1], ['All']]});
	    this.table.draw();
	    this.table.order(order);
	    this.table.search(searchStr);
	    this.table.draw();
	    this.$('#listTable_wrapper').prepend("<div id='listTable_length' class='dataTables_length'></div>");
	    this.$("#listTable_length").empty();
	    this.$(".dataTables_filter").css('left', 10);
	    this.$(".dataTables_filter").css('right', 'auto');
    },

    addStory: function(){
        var model = new Story({user:me.id});
        var view = new StoryEditView({el: this.editDialog, model: model, isDialog: true});
        this.editDialog.view = view;
        this.editDialog.dialog({
            title: "<span class='en'>Share a Case or Experience</span><span class='fr'>Partager un Cas ou de L'experience</span>",
            height: $(window).height()*0.80,
            width: 650
        });
        this.editDialog.dialog('open');
    },
    
    events: {
        "click #addStoryButton": "addStory",
    },

    render: function(){
        this.$el.empty();
        var mod = _.extend(this.model.toJSON(), me);
        this.el.innerHTML = this.template(mod);
        this.addRows();
	    var text = "Share Case or Experience";
	    if(wgLang == 'fr'){
	        text = "Partager le Cas ou de L'experience";
	    }
        this.editDialog = this.$("#editDialog").dialog({
	        autoOpen: false,
	        modal: true,
	        show: 'fade',
	        resizable: false,
	        draggable: false,
	        width: "550px",
	        open: function(){
	            $("html").css("overflow", "hidden");
	        },
	        beforeClose: function(){
	            $("html").css("overflow", "auto");
	        },
	        buttons: [
		     {
			   text: text,
                           click: $.proxy(function(){
                           this.editDialog.view.model.save(null, {
                                success: $.proxy(function(){
                                    this.$(".throbber").hide();
                                    this.$("#saveThread").prop('disabled', false);
                                    clearAllMessages();
                                    document.location = wgServer + wgScriptPath + "/index.php/Special:StoryManagePage";

                            }, this),
                            error: $.proxy(function(m, e){
                                this.$(".throbber").hide();
                                clearAllMessages();
                                addError(e.responseText, true);
                            }, this)
                        });
                    }, this)
		    }
	        ]
	    });
        return this.$el;
    }
});
