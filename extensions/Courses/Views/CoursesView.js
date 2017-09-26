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
        //var model = new Course({student_person: [me.toJSON()]});
        var model = new Course();
	model.set('person_name',me.get('fullName'));
	model.set('student_url', me.get('url'));
        var view = new CoursesEditView({el: this.editDialog, model: model, isDialog: true});
	view.render();
        this.editDialog.view = view;
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
        $(document).click($.proxy(function(e){
            var popup = $("div.popupBox:visible").not(":animated").first();
            if(popup.length > 0 && !$.contains(popup[0], e.target)){
                _.each(this.subViews, function(view){
                    if(view.$("div.popupBox").is(":visible")){
                        // Need to defer the event so that unchecking a project is not in conflict
                        _.defer(function(){
                            view.model.trigger("change", view.model);
                        });
                    }
                });
            }
        }, this));
        this.$el.html(this.template());
	console.log(this.model);
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
                $(document).ready(function () {
                    $("#showfilter").click(function () {
                        if ($(this).data('name') == 'show') {
                            $("#filters").animate({
                            }).hide();
                            $(this).data('name', 'hide');
                            $(this).val('Show Filter Options');
                        } else {
                            $("#filters").animate({
                            }).show();
                            $(this).data('name', 'show')
                            $(this).val('Hide Filter Options');
                        }
                    });
                });
            $(window).resize($.proxy(function(){
                this.editDialog.dialog({height: $(window).height()*0.75});
            }, this));
        return this.$el;
    }
});
