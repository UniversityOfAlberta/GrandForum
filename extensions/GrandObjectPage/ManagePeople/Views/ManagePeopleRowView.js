ManagePeopleRowView = Backbone.View.extend({
    
    tagName: 'tr',
    parent: null,
    row: null,
    // Views
    editRoles: null,
    editRelations: null,
    editUniversities: null,
    editAlumni: null,
    editSubRoles: null,
    editProjectLeaders: null,
    editThemeLeaders: null,
    // Dialogs
    rolesDialog: null,
    relationsDialog: null,
    universitiesDialog: null,
    editAlumni: null,
    subRolesDialog: null,
    projectLeadersDialog: null,
    themeLeadersDialog: null,
    deleteDialog: null,
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
	            beforeClose: function(){
	                $("html").css("overflow", "auto");
	                this.editRoles.stopListening();
	                this.editRoles.undelegateEvents();
	                clearInterval(this.editRoles.interval);
	                this.editRoles.interval = null;
	            }.bind(this),
	            buttons: {
	                "+": { 
	                    text: "Add Role", 
	                    click: function(e){
	                        this.editRoles.addRole();
	                    }.bind(this), 
	                    disabled: (allowedRoles.length == 0 || (this.model.get('candidate') && _.intersection(_.pluck(me.get('roles'), 'role'), [STAFF,MANAGER,ADMIN]).length == 0)),
	                    style: "float: left;"
	                },
	                "Save": function(e){
	                    this.editRoles.saveAll();
                        this.rolesDialog.dialog('close');
	                }.bind(this),
	                "Cancel": function(){
	                    this.rolesDialog.dialog('close');
	                }.bind(this)
	            }
	        });
	    }
        this.rolesDialog.empty();
        this.rolesDialog.dialog('open');
        this.editRoles = new ManagePeopleEditRolesView({model: this.model.roles, 
                                                        person:this.model, 
                                                        el: this.rolesDialog});
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
	            beforeClose: function(){
	                $("html").css("overflow", "auto");
	                this.editRelations.stopListening();
	                this.editRelations.undelegateEvents();
	                clearInterval(this.editRelations.interval);
	                this.editRelations.interval = null;
	            }.bind(this),
	            buttons: {
	                "+": { 
	                    text: "Add Relationship", 
	                    click: function(e){
	                        this.editRelations.addRelation();
	                    }.bind(this), 
	                    style: "float: left;"
	                },
	                "Save": function(e){
	                    this.editRelations.saveAll();
                        this.relationsDialog.dialog('close');
	                }.bind(this),
	                "Cancel": function(){
	                    this.relationsDialog.dialog('close');
	                }.bind(this)
	            }
	        });
        }
        this.relationsDialog.empty();
        this.relationsDialog.dialog('open');
        this.editRelations = new ManagePeopleEditRelationsView({model: me.relations, 
                                                                person:this.model, 
                                                                el: this.relationsDialog});
        this.editRelations.allPeople = this.parent.allPeople;
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
	            beforeClose: function(){
	                $("html").css("overflow", "auto");
	                this.editUniversities.stopListening();
	                this.editUniversities.undelegateEvents();
	                clearInterval(this.editUniversities.interval);
	                this.editUniversities.interval = null;
	            }.bind(this),
	            buttons: {
	                "+": { 
	                    text: "Add Institution", 
	                    click: function(e){
	                        this.editUniversities.addUniversity();
	                    }.bind(this), 
	                    style: "float: left;"
	                },
	                "Save": function(e){
	                    this.editUniversities.saveAll();
                        this.universitiesDialog.dialog('close');
	                }.bind(this),
	                "Cancel": function(){
	                    this.universitiesDialog.dialog('close');
	                }.bind(this)
	            }
	        });
        }
        this.universitiesDialog.empty();
        this.universitiesDialog.dialog('open');
        this.editUniversities = new ManagePeopleEditUniversitiesView({model: this.model.universities, 
                                                                      person:this.model,
                                                                      el: this.universitiesDialog});
    },
    
    openAlumniDialog: function(){
        if(this.alumniDialog == null){
            this.alumniDialog = this.$("#alumniDialog").dialog({
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
	            beforeClose: function(){
	                $("html").css("overflow", "auto");
	                this.editAlumni.stopListening();
	                this.editAlumni.undelegateEvents();
	                clearInterval(this.editAlumni.interval);
	                this.editAlumni.interval = null;
	            }.bind(this),
	            buttons: {
	                "Save": function(e){
	                    this.editAlumni.model.save(null, {
	                        success: function(){
	                            clearAllMessages();
	                            addSuccess("Recruitment / Alumni saved");
	                        },
	                        error: function(){
	                            clearAllMessages();
	                            addError("Could not modify Recruitment / Alumni");
	                        }
	                    });
                        this.alumniDialog.dialog('close');
	                }.bind(this),
	                "Cancel": function(){
	                    this.alumniDialog.dialog('close');
	                }.bind(this)
	            }
	        });
	    }
        this.alumniDialog.empty();
        this.alumniDialog.dialog('open');
        this.editAlumni = new ManagePeopleEditAlumniView({model: this.model.alumni,
                                                          el: this.alumniDialog});
    },
    
    openSubRolesDialog: function(){
        if(this.subRolesDialog == null){
            this.subRolesDialog = this.$("#subRolesDialog").dialog({
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
	            beforeClose: function(){
	                $("html").css("overflow", "auto");
	                this.editSubRoles.stopListening();
	                this.editSubRoles.undelegateEvents();
	                clearInterval(this.editSubRoles.interval);
	                this.editSubRoles.interval = null;
	            }.bind(this),
	            buttons: {
	                "Save": function(e){
	                    this.editSubRoles.model.save(null, {
	                        success: function(){
	                            clearAllMessages();
	                            addSuccess(subRolesTerm.pluralize() + " saved");
	                        },
	                        error: function(){
	                            clearAllMessages();
	                            addError("Could not modify " + subRolesTerm.pluralize());
	                        }
	                    });
                        this.subRolesDialog.dialog('close');
	                }.bind(this),
	                "Cancel": function(){
	                    this.subRolesDialog.dialog('close');
	                }.bind(this)
	            }
	        });
	    }
        this.subRolesDialog.empty();
        this.subRolesDialog.dialog('open');
        this.editSubRoles = new ManagePeopleEditSubRolesView({model: this.model.subRoles,
                                                              el: this.subRolesDialog});
    },

    openThemeLeadersDialog: function(){
        if(this.themeLeadersDialog == null){
            this.themeLeadersDialog = this.$("#themeLeadershipDialog").dialog({
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
	            beforeClose: function(){
	                $("html").css("overflow", "auto");
	                this.editThemeLeaders.stopListening();
	                this.editThemeLeaders.undelegateEvents();
	                clearInterval(this.editThemeLeaders.interval);
	                this.editThemeLeaders.interval = null;
	            }.bind(this),
	            buttons: {
	                "+": { 
	                    text: "Add Theme", 
	                    click: function(e){
	                        this.editThemeLeaders.addTheme();
	                    }.bind(this),
	                    disabled: (allowedThemes.length == 0),
	                    style: "float: left;"
	                },
	                "Save": function(e){
	                    this.editThemeLeaders.saveAll();
                        this.themeLeadersDialog.dialog('close');
	                }.bind(this),
	                "Cancel": function(){
	                    this.themeLeadersDialog.dialog('close');
	                }.bind(this)
	            }
	        });
        }
        this.themeLeadersDialog.empty();
        this.themeLeadersDialog.dialog('open');
        this.editThemeLeaders = new ManagePeopleEditThemeLeadersView({model: this.model.themes, 
                                                                      person:this.model, 
                                                                      el: this.themeLeadersDialog});
    },
    
    openDeleteDialog: function(){
        if(this.deleteDialog == null){
            this.deleteDialog = this.$("#deleteDialog").dialog({
	            autoOpen: false,
	            modal: true,
	            show: 'fade',
	            resizable: false,
	            draggable: false,
	            open: function(){
	                $("html").css("overflow", "hidden");
	            },
	            beforeClose: function(){
	                $("html").css("overflow", "auto");
	            },
	            buttons: {
	                "Delete": function(){
	                    var model = this.model;
                        $("div.throbber", this.deleteDialog).show();
                        model.destroy({
                            success: function(model, response) {
                                this.deleteDialog.dialog('close');
                                $("div.throbber", this.deleteDialog).hide();
                                clearSuccess();
                                clearError();
                                addSuccess('The user was deleted sucessfully');
                            }.bind(this),
                            error: function(model, response) {
                                this.deleteDialog.dialog('close');
                                clearSuccess();
                                clearError();
                                addError('The user was not deleted sucessfully');
                            }.bind(this)
                        });
	                }.bind(this),
	                "Cancel": function(){
	                    this.deleteDialog.dialog('close');
	                }.bind(this)
	            }
	        });
	    }
        this.deleteDialog.dialog('open');
    },
    
    save: function(){
        _.defer(function(){
            this.$(".throbber").show();
        }.bind(this));
        this.model.save(null, {
            success: function(){
                this.$(".throbber").hide();
            }.bind(this),
            error: function(){
                this.$(".throbber").hide();
            }.bind(this),
            silent: true
        });
    },
    
    events: {
        "click #editRoles": "openRolesDialog",
        "click #editRelations": "openRelationsDialog",
        "click #editUniversities": "openUniversitiesDialog",
        "click #editAlumni": "openAlumniDialog",
        "click #editSubRoles": "openSubRolesDialog",
        "click #editThemeLeadership": "openThemeLeadersDialog",
        "click .delete-icon": "openDeleteDialog"
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
