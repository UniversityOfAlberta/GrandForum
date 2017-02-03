MyThreadsView = Backbone.View.extend({

    table: null,
    threads: null,
    editDialog: null,

    initialize: function(){
        this.template = _.template($('#my_threads_template').html());
        this.listenTo(this.model, "sync", function(){
            this.threads = this.model;
            this.listenTo(this.threads, "addThread", this.addRows);
            this.listenTo(this.threads, "remove", this.addRows);
            this.render();
        }, this);
    },

    addThread: function(){
        var model = new Thread({author: {id: me.id, name:me.get('name'), url:me.get('url')}});
	    var model2 = new Post({'user_id':me.id});
        var view = new ThreadEditView({el: $('#editThreadView', this.editDialog), model: model, isDialog: true});
	    var view2 = new PostView({el: $('#editPostView', this.editDialog), model: model2, isDialog: true});
        this.editDialog.view = view;
	    this.editDialog.view2 = view2;
        if(_.findWhere(me.get('roles'), {"role":"Admin"}) != undefined ||  _.findWhere(me.get('roles'), {"role":"Manager"}) != undefined){ 
	        this.editDialog.dialog({
     		height: $(window).height()*0.75,
                	width: 800,
                	title: "<span class='en'>Ask an Expert</span><span class='fr'>Demandez à un Expert</span>"
                });
	    }
	    else{
            this.editDialog.dialog({
                height: $(window).height()*0.60,
                width: 650,
                title: "<span class='en'>Ask an Expert</span><span class='fr'>Demandez à un Expert</span>"
            });
	    }
        this.editDialog.dialog('open');
    },
    
    addRows: function(){
        this.threads.each($.proxy(function(p, i){
            var row = new MyThreadsRowView({model: p, parent: this});
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
    },

    events: {
	"click #addThreadButton" : "addThread",
    },

    render: function(){
        this.$el.empty();
        this.$el.html(this.template());
        this.addRows();
        var text = "Save";
        if(wgLang == "fr"){
            text = "Enregistrer";
        }
        this.editDialog = this.$("#editDialog").dialog({
                autoOpen: false,
                modal: true,
                show: 'fade',
                resizable: false,
                draggable: false,
                open: function(){
                    $("html").css("overflow", "hidden");
                },
                beforeClose: $.proxy(function(){
                    this.editDialog.view.stopListening();
                    this.editDialog.view.undelegateEvents();
                    this.editDialog.view.$el.empty();
                    $("html").css("overflow", "auto");
                }, this),
                buttons: [
                    {
                        text: text,
                        click: $.proxy(function(){
                        var m = this.editDialog.view.model.save(null, {
                            success: $.proxy(function(){
				                this.$(".throbber").hide();
                		        this.$("#saveThread").prop('disabled', false);
				                this.editDialog.view2.model.set("thread_id", m.responseJSON.id);
				                this.editDialog.view2.model.save(null, {
                            	    success: $.proxy(function(){
                                    	this.$(".throbber").hide();
                                    	this.$("#saveThread").prop('disabled', false);
                                    	this.editDialog.dialog("close");
                                    	clearAllMessages();
                                        addSuccess("Thread has been successfully saved");
                                    	document.location = wgServer + wgScriptPath + "/index.php/Special:MyThreads";		
                            	    }, this),
                            	    error: $.proxy(function(){
                                    	this.$(".throbber").hide();
                                    	clearAllMessages();
                                    	addError("There was a problem saving the Post", true);
                            	    }, this)
                            	});
                            }, this),
                            error: $.proxy(function(){
                        		this.$(".throbber").hide();
                        		clearAllMessages();
                        		addError("There was a problem saving the Thread", true);
                            }, this)
                       });
                    }, this)
                }
            ]
            });

        return this.$el;
    }
});
