CoursesView = Backbone.View.extend({

    table: null,
    sops: null,
    editDialog: null,
    lastTimeout: null,

    initialize: function(){
        this.template = _.template($('#courses_template').html());
        this.listenTo(this.model, "sync", function(){
            this.sops = this.model;
            this.render();
        }, this);
    },

    addCourse: function(){
        var model = new Course();
	//this sets it so that person logged in is added as owner of course.. maybe later can add actual field for Admins to set
	model.set('person_name',me.get('fullName'));
	model.set('student_url', me.get('url'));
        var view = new CoursesEditView({el: this.editDialog, model: model, isDialog: true});
        this.editDialog.view = view;
        view.render();
        this.editDialog.dialog({
            height: $(window).height()*0.75,
            width: 800,
            title: "Create Course"
        });
        this.editDialog.dialog('open');
    },
    
    addRows: function(){
        if(this.table != undefined){
            this.table.destroy();
        }
        this.sops.each($.proxy(function(p, i){
            var row = new CoursesRowView({model: p, parent: this});
            this.$("#coursesRows").append(row.$el);
            row.render();
        }, this));
        this.createDataTable();
    },
    
    createDataTable: function(){
        this.table = this.$('#listTable').DataTable({'bPaginate': false,
                                                     'bFilter': true,
                                                     'autoWidth': false,
                                                     'aLengthMenu': [[-1], ['All']]});
        this.table.draw();
        this.$('#listTable_wrapper').prepend("<div id='listTable_length' class='dataTables_length'></div>");
    },

    events: {
        "click #addCourseButton": "addCourse",
    },

    render: function(){
        this.$el.empty();
        this.$el.html(this.template());
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
                        text: "Save Course",
                        click: $.proxy(function(){
                        var validation = "";
                        if(validation != ""){
                            clearAllMessages("#dialogMessages");
                            addError(validation, true, "#dialogMessages");
                            return "";
                        }
                        this.editDialog.view.model.save(null, {
                            success: $.proxy(function(){
                                var product = this.editDialog.view.model;
                            }, this),
                            error: $.proxy(function(o, e){
                                clearAllMessages("#dialogMessages");
                                if(e.responseText != ""){
                                    addError(e.responseText, true, "#dialogMessages");
                                }
                                else{
                                    addError("There was a problem saving the course", true, "#dialogMessages");
                                }
                            }, this)
                        });
                    }, this)
                }
            ]
            });

        this.addRows();
        $(window).resize($.proxy(function(){
            this.editDialog.dialog({height: $(window).height()*0.75});
        }, this));
        return this.$el;
    }
});
