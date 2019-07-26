BoardView = Backbone.View.extend({

    table: null,
    threads: null,
    editDialog: null,
    lastTimeout: null,

    initialize: function(){
        this.threads = new Threads();
        this.template = _.template($('#board_template').html());
        this.listenTo(this.model, "sync", function(){
            this.threads.board_id = this.model.get('id');
            this.threads.fetch();
        }, this);
        this.listenTo(this.threads, "sync", function(){
            this.render();
        });
    },
    
    checkEnter: function(e){
        clearTimeout(this.lastTimeout);
        if(e.keyCode == 13){ // ENTER
            this.$("#advancedSearchButton").click();
        }
        else{
            this.lastTimeout = setTimeout(function(){
                this.$("#advancedSearchButton").click();
            }.bind(this), 500);
        }
    },
    
    doSearch: function(){
        this.$("#advancedSearchButton").prop('disabled', true);
        this.threads.search = this.$('#advancedSearch').val();
        this.threads.fetch();
    },

    addThread: function(){
        var model = new Thread({board_id: this.model.get('id'), author: {id: me.id, name:me.get('name'), url:me.get('url')}});
        var model2 = new Post({'user_id':me.id});
        var view = new ThreadEditView({el: $('#editThreadView', this.editDialog), model: model, isDialog: true});
        var view2 = new PostView({el: $('#editPostView', this.editDialog), model: model2, isDialog: true});
        this.editDialog.view = view;
        this.editDialog.view2 = view2;
        this.editDialog.dialog({
            height: $(window).height()*0.65,
            width: 800,
            title: "Create Thread"
        });
        this.editDialog.dialog('open');
    },
    
    addRows: function(){
        if(this.table != undefined){
            this.table.destroy();
        }
        var onlyStickies = (this.threads.where({'stickied': "0"}).length == 0);
        this.threads.each(function(p, i){
            var row = new MyThreadsRowView({model: p, parent: this});
            if(p.get('stickied') == 1 && !onlyStickies){
                this.$("#stickies").append(row.$el);
            }
            if(p.get('stickied') == 0 || onlyStickies){
                this.$("#threads").append(row.$el);
            }
            row.render();
        }.bind(this));
        this.createDataTable();
        this.$("#advancedSearchButton").prop('disabled', false);
    },
    
    createDataTable: function(){
        var stickies = this.$("#stickies tr").detach();
        this.$("#stickies").remove();
        this.table = this.$('#listTable').DataTable({'bPaginate': false,
                                                     'bFilter': false,
                                                     'autoWidth': false,
                                                     'aLengthMenu': [[-1], ['All']],
                                                     'drawCallback': function(settings){
            // Make sure sticky threads remain at the top
            if(this.$('#listTable tbody tr:first').length > 0){
                this.$('#listTable tbody tr:first').before(stickies);
            }
            else{
                this.$('#listTable tbody').html(stickies);
            }
        }});
        this.table.draw();
        if(networkName == "GlycoNet"){
            this.table.order([3, 'desc']);
        }
        else{
            this.table.order([4, 'desc']);
        }
        this.table.draw();
        this.$('#listTable_wrapper').prepend("<div id='listTable_length' class='dataTables_length'></div>");
        this.$('#listTable_length').html($("#advancedSearchButton").parent());
    },

    events: {
        "click #addThreadButton" : "addThread",
        "click #advancedSearchButton" : "doSearch",
        "keydown #advancedSearch" : "checkEnter"
    },

    render: function(){
        main.set('title', "<a href='" + wgServer + wgScriptPath + "/index.php/Special:MyThreads'>Message Boards</a> &gt; " + this.model.get('title'));
        var caret = {start: 0, end: 0, text: "", replace: function(){}};
        if(this.$("#advancedSearch").length > 0){
            caret = this.$("#advancedSearch").caret();
        }
        this.$el.empty();
        this.$el.html(this.template(this.model.toJSON()));
        this.addRows();
        this.$("#advancedSearch").focus();
        this.$("#advancedSearch").caret(caret);
        this.editDialog = this.$("#editDialog").dialog({
                autoOpen: false,
                modal: true,
                show: 'fade',
                resizable: false,
                draggable: false,
                open: function(){
                    $("html").css("overflow", "hidden");
                },
                beforeClose: function(){
                    this.editDialog.view.stopListening();
                    this.editDialog.view.undelegateEvents();
                    this.editDialog.view.$el.empty();
                    $("html").css("overflow", "auto");
                }.bind(this),
                buttons: [
                    {
                        text: "Save Thread",
                        click: function(){
                        var m = this.editDialog.view.model.save(null, {
                            success: function(){
                                this.$(".throbber").hide();
                                this.$("#saveThread").prop('disabled', false);
                                this.editDialog.view2.model.set("thread_id", m.responseJSON.id);
                                this.editDialog.view2.model.save(null, {
                                    success: function(){
                                        this.$(".throbber").hide();
                                        this.$("#saveThread").prop('disabled', false);
                                        this.editDialog.dialog("close");
                                        clearAllMessages();
                                        this.model.fetch();
                                        addSuccess("Thread has been successfully saved");
                                    }.bind(this),
                                    error: function(){
                                        this.$(".throbber").hide();
                                        clearAllMessages();
                                        addError("There was a problem saving the Post", true);
                                    }.bind(this)
                                });
                            }.bind(this),
                            error: function(){
                                this.$(".throbber").hide();
                                clearAllMessages();
                                addError("There was a problem saving the Thread", true);
                            }.bind(this)
                        });
                    }.bind(this)
                }
            ]
        });
        return this.$el;
    }
});
