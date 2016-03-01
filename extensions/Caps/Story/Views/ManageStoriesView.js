ManageStoriesView = Backbone.View.extend({

    table: null,
    subViews: new Array(),
    stories: null,
    newStoryDialog: null,

    initialize: function(){
        this.template = _.template($('#manage_stories_template').html());
        this.listenTo(this.model, "sync", function(){
            this.stories = this.model;
            this.listenTo(this.stories, "add", this.addRows);
            this.listenTo(this.stories, "remove", this.addRows);
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
        // First remove deleted models
        _.each(this.subViews, $.proxy(function(view){
            var m = view.model;
            if(this.stories.where({id: m.get('id')}).length == 0){
                this.subViews = _.without(this.subViews, view);
                view.remove();
            }
        }, this));
        // Then add new ones
        var models = _.pluck(_.pluck(this.subViews, 'model'), 'id');
        this.stories.each($.proxy(function(p, i){
            if(!_.contains(models, p.id)){
                var row = new ManageStoriesRowView({model: p, parent: this});
                this.subViews.push(row);
                this.$("#personRows").append(row.$el);
            }
        }, this));
        _.each(this.subViews, function(row){
            row.render();
        });
        var end = new Date();
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
    },
    
    addNewStory: function(){
        this.addNewStoryDialog.dialog('open');
        this.addNewStoryDialog.parent().css('overflow', 'visible');
    },

    submitStory: function(){
        this.model.save(null, {
            success: $.proxy(function(){
                clearAllMessages();
            }, this),
            error: $.proxy(function(){
                clearAllMessages();
                addError("There was a problem saving the Story", true);
            }, this)
        });

    },
    
    events: {
        "click #addNewStory": "addNewStory",
    },

    render: function(){
        this.$el.empty();
        this.$el.html(this.template());
        this.addRows();
        this.addNewStoryDialog = this.$("#addNewStoryDialog").dialog({
	        autoOpen: false,
	        modal: true,
	        show: 'fade',
	        resizable: false,
	        draggable: false,
	        width: "550px",
	        position: {
                my: "center bottom",
                at: "center center"
            },
	        open: function(){
	            $("html").css("overflow", "hidden");
	        },
	        beforeClose: function(){
	            $("html").css("overflow", "auto");
	        },
	        buttons: {
	            "Add":{
			   text: "Submit Story",
			   click: $.proxy(function(e){
				//here is where the model is trying to be saved but cannot cuz it is not a story
				//it is stories atm cuz the page is being used not the dialog box
        		/*	this.model.save(null, {
            			    success: $.proxy(function(){
                		    	clearAllMessages();
            			    }, this),
            			    error: $.proxy(function(){
                		    	clearAllMessages();
                			addError("There was a problem saving the Story", true);
            			    }, this)
        	                 });
	                  */
			   //this line actually saves it at the moment but it doesn't work well 	
			    $.post(wgServer + wgScriptPath + "/index.php?action=api.story")
	                    .done($.proxy(function(){
	                   }, this))
	                    .fail($.proxy(function(){
	                    addError("There was a problem adding this story");
	                   }, this));
                    this.addNewStoryDialog.dialog('close');
	            }, this)
		    },
	            "Cancel": $.proxy(function(){
	                this.addNewStoryDialog.dialog('close');
	            }, this)
	        }
	    });
        return this.$el;
    }
});
