ManagePeopleRowView = Backbone.View.extend({
    
    tagName: 'tr',
    parent: null,
    row: null,
    // Views
    editRoles: null,
    editProjects: null,
    editRelations: null,
    editUniversities: null,
    // Dialogs
    rolesDialog: null,
    projectsDialog: null,
    relationsDialog: null,
    universitiesDialog: null,
    template: _.template($('#manage_people_row_template').html()),
    
    initialize: function(options){
        this.parent = options.parent;
        this.listenTo(this.model, "change", this.render);
        this.listenTo(this.model, "change:candidate", this.save);
    },
    
    openRolesDialog: function(){
        if(this.rolesDialog == null){
            this.rolesDialog = this.$("#rolesDialog").dialog({
	            autoOpen: false,
	            modal: true,
	            show: 'fade',
	            resizable: false,
	            draggable: false,
	            width: 800,
	            position: {
                    my: "center bottom",
                    at: "center center"
                },
	            open: function(){
	                $("html").css("overflow", "hidden");
	            },
	            beforeClose: $.proxy(function(){
	                $("html").css("overflow", "auto");
	                this.editRoles.stopListening();
	                this.editRoles.undelegateEvents();
	                clearInterval(this.editRoles.interval);
	                this.editRoles.interval = null;
	            }, this),
	            buttons: {
	                "+": { 
	                    text: "Add Role", 
	                    click: $.proxy(function(e){
	                        this.editRoles.addRole();
	                    }, this), 
	                    disabled: (allowedRoles.length == 0 || (this.model.get('candidate') && _.intersection(_.pluck(me.get('roles'), 'role'), [STAFF,MANAGER,ADMIN]).length == 0)),
	                    style: "float: left;"
	                },
	                "Save": $.proxy(function(e){
	                    this.editRoles.saveAll();
                        this.rolesDialog.dialog('close');
	                }, this),
	                "Cancel": $.proxy(function(){
	                    this.rolesDialog.dialog('close');
	                }, this)
	            }
	        });
	    }
        this.rolesDialog.empty();
        this.rolesDialog.dialog('open');
        this.editRoles = new ManagePeopleEditRolesView({model: this.model.roles, 
                                                        person:this.model, 
                                                        el: this.rolesDialog});
    },
    
    openProjectsDialog: function(){
        if(this.projectsDialog == null){
            this.projectsDialog = this.$("#projectsDialog").dialog({
	            autoOpen: false,
	            modal: true,
	            show: 'fade',
	            resizable: false,
	            draggable: false,
	            width: 800,
	            position: {
                    my: "center bottom",
                    at: "center center"
                },
	            open: function(){
	                $("html").css("overflow", "hidden");
	            },
	            beforeClose: $.proxy(function(){
	                $("html").css("overflow", "auto");
	                this.editProjects.stopListening();
	                this.editProjects.undelegateEvents();
	                clearInterval(this.editProjects.interval);
	                this.editProjects.interval = null;
	            }, this),
	            buttons: {
	                "+": { 
	                    text: "Add Project", 
	                    click: $.proxy(function(e){
	                        this.editProjects.addProject();
	                    }, this),
	                    disabled: (allowedProjects.length == 0),
	                    style: "float: left;"
	                },
	                "Save": $.proxy(function(e){
	                    this.editProjects.saveAll();
                        this.projectsDialog.dialog('close');
	                }, this),
	                "Cancel": $.proxy(function(){
	                    this.projectsDialog.dialog('close');
	                }, this)
	            }
	        });
        }
        this.projectsDialog.empty();
        this.projectsDialog.dialog('open');
        this.editProjects = new ManagePeopleEditProjectsView({model: this.model.projects, 
                                                              person:this.model, 
                                                              el: this.projectsDialog});
    },
    
    openRelationsDialog: function(){
        if(this.relationsDialog == null){
            this.relationsDialog = this.$("#relationsDialog").dialog({
	            autoOpen: false,
	            modal: true,
	            show: 'fade',
	            resizable: false,
	            draggable: false,
	            width: 800,
	            position: {
                    my: "center bottom",
                    at: "center center"
                },
	            open: function(){
	                $("html").css("overflow", "hidden");
	            },
	            beforeClose: $.proxy(function(){
	                $("html").css("overflow", "auto");
	                this.editRelations.stopListening();
	                this.editRelations.undelegateEvents();
	                clearInterval(this.editRelations.interval);
	                this.editRelations.interval = null;
	            }, this),
	            buttons: {
	                "+": { 
	                    text: "Add Relationship", 
	                    click: $.proxy(function(e){
	                        this.editRelations.addRelation();
	                    }, this), 
	                    style: "float: left;"
	                },
	                "Save": $.proxy(function(e){
	                    this.editRelations.saveAll();
                        this.relationsDialog.dialog('close');
	                }, this),
	                "Cancel": $.proxy(function(){
	                    this.relationsDialog.dialog('close');
	                }, this)
	            }
	        });
        }
        this.relationsDialog.empty();
        this.relationsDialog.dialog('open');
        this.editRelations = new ManagePeopleEditRelationsView({model: me.relations, 
                                                                person:this.model, 
                                                                el: this.relationsDialog});
    },
    
    openUniversitiesDialog: function(){
        if(this.universitiesDialog == null){
            this.universitiesDialog = this.$("#universitiesDialog").dialog({
	            autoOpen: false,
	            modal: true,
	            show: 'fade',
	            resizable: false,
	            draggable: false,
	            width: 800,
	            position: {
                    my: "center bottom",
                    at: "center center"
                },
	            open: function(){
	                $("html").css("overflow", "hidden");
	            },
	            beforeClose: $.proxy(function(){
	                $("html").css("overflow", "auto");
	                this.editUniversities.stopListening();
	                this.editUniversities.undelegateEvents();
	                clearInterval(this.editUniversities.interval);
	                this.editUniversities.interval = null;
	            }, this),
	            buttons: {
	                "+": { 
	                    text: "Add Institution", 
	                    click: $.proxy(function(e){
	                        this.editUniversities.addUniversity();
	                    }, this), 
	                    style: "float: left;"
	                },
	                "Save": $.proxy(function(e){
	                    this.editUniversities.saveAll();
                        this.universitiesDialog.dialog('close');
	                }, this),
	                "Cancel": $.proxy(function(){
	                    this.universitiesDialog.dialog('close');
	                }, this)
	            }
	        });
        }
        this.universitiesDialog.empty();
        this.universitiesDialog.dialog('open');
        this.editUniversities = new ManagePeopleEditUniversitiesView({model: this.model.universities, 
                                                                      person:this.model,
                                                                      el: this.universitiesDialog});
    },
    
    save: function(){
        _.defer($.proxy(function(){
            this.$(".throbber").show();
        }, this));
        this.model.save(null, {
            success: $.proxy(function(){
                this.$(".throbber").hide();
            }, this),
            error: $.proxy(function(){
                this.$(".throbber").hide();
            }, this),
            silent: true
        });
    },
    
    events: {
        "click #editRoles": "openRolesDialog",
        "click #editProjects": "openProjectsDialog",
        "click #editRelations": "openRelationsDialog",
        "click #editUniversities": "openUniversitiesDialog"
    },
    
    render: function(){
        var classes = new Array();
        this.$("td").each(function(i, val){
            classes.push($(val).attr("class"));
        });
        this.el.innerHTML = this.template(this.model.toJSON());
        if(this.parent.table != null){
            var data = new Array();
            this.$("td").each(function(i, val){
                data.push($(val).htmlClean().html());
            });
            if(this.row != null){
                this.row.data(data);
            }
        }
        if(classes.length > 0){
            this.$("td").each(function(i, val){
                $(val).addClass(classes[i]);
            });
        }
        return this.$el;
    }
    
});
