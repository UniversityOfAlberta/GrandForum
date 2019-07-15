ManagePeopleRowView = Backbone.View.extend({
    
    tagName: 'tr',
    parent: null,
    row: null,
    // Views
    editRoles: null,
    editRelations: null,
    editUniversities: null,
    // Dialogs
    rolesDialog: null,
    relationsDialog: null,
    universitiesDialog: null,
    template: _.template($('#manage_people_row_template').html()),
    
    initialize: function(options){
        this.parent = options.parent;
        this.listenTo(this.model, "change", this.render);
        this.listenTo(this.model, "sync", this.render);
    },
    
    initializeRolesDialog: function(){
        if(this.rolesDialog == null){
            this.rolesDialog = this.$("#rolesDialog"+this.model.id).dialog({
                autoOpen: false,
                modal: true,
                show: 'fade',
                resizable: false,
                draggable: false,
                width: 'auto',
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
                }.bind(this),
                buttons: {
                    "+": { 
                        text: "Add Role", 
                        click: function(e){
                            this.editRoles.addRole();
                        }.bind(this), 
                        disabled: (allowedRoles.length == 0),
                        style: "position:absolute;left:0;"
                    },
                    "Save": { 
                text: "Save",
                id: "SaveButton"+this.model.id,
                    click: function(e){
                        this.editRoles.saveAll();
                        this.rolesDialog.dialog('close');
                    }.bind(this)
                },
                    "Cancel": function(){
                        this.rolesDialog.dialog('close');
                    }.bind(this)
                }
            });
        }
    },
    
    initializeRelationsDialog: function(){
        if(this.relationsDialog == null){
            this.relationsDialog = this.$("#relationsDialog").dialog({
                autoOpen: false,
                modal: true,
                show: 'fade',
                resizable: false,
                draggable: false,
                width: 'auto',
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
                }.bind(this),
                buttons: {
                    "+": { 
                        text: "Add Relationship", 
                        click: function(e){
                            this.editRelations.addRelation();
                        }.bind(this), 
                        style: "position:absolute;left:0;"
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
    },
    
    initializeUniversitiesDialog: function(){
        if(this.universitiesDialog == null){
            this.universitiesDialog = this.$("#universitiesDialog").dialog({
                autoOpen: false,
                modal: true,
                show: 'fade',
                resizable: false,
                draggable: false,
                width: 'auto',
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
                }.bind(this),
                buttons: {
                    "+": {
                        text: "Add University",
                        id: "AddUniButton"+this.model.id, 
                        click: function(e){
                            this.editUniversities.addUniversity();
                        }.bind(this), 
                        style: "position:absolute;left:0;"
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
    },
    
    openRolesDialog: function(){
        this.initializeRolesDialog();
        this.rolesDialog.empty();
        this.rolesDialog.dialog('open');
        this.editRoles = new ManagePeopleEditRolesView({model: this.model.roles, 
                                                        person:this.model, 
                                                        el: this.rolesDialog});
    },
    
    openRelationsDialog: function(){
        this.initializeRelationsDialog();
        this.relationsDialog.empty();
        this.relationsDialog.dialog('open');
        this.editRelations = new ManagePeopleEditRelationsView({model: me.relations, 
                                                                person:this.model, 
                                                                el: this.relationsDialog});
    },
    
    openUniversitiesDialog: function(){
        this.initializeUniversitiesDialog();
        this.universitiesDialog.empty();
        this.universitiesDialog.dialog('open');
        this.editUniversities = new ManagePeopleEditUniversitiesView({model: this.model.universities, 
                                                                      person:this.model,
                                                                      el: this.universitiesDialog});
    },
    
    removePerson: function(){
        $.post(wgServer + wgScriptPath + "/index.php?action=api.people/managed", {'_method': 'DELETE', 'model': '{"id":' + this.model.get('id') + '}'});
        this.parent.people.remove(this.model);
    },
    
    events: {
        "click #editRoles": "openRolesDialog",
        "click #editRelations": "openRelationsDialog",
        "click #editUniversities": "openUniversitiesDialog",
        "click .delete-icon": "removePerson"
    },
    
    renderRelationType: function(){
        if(!me.relations.fetching()){ // Only Fetch once
            me.getRelations();
        }
        me.relations.ready().then(function(){
            var universities = this.model.get('universities');
            var relations = new PersonRelations(me.relations.where({user2: this.model.get('id')}));
            var start = ""; this.model.get('start');
            var end = ""; this.model.get('end');
            var position = this.model.get('position');
            
            var latestRel = null;
            if(relations.length == 0){
                // No Relations with this person, so the 'Remove' icon
                this.$(".delete-icon").show();
            }
            else{
                // This will probably already be hidden, but just incase
                this.$(".delete-icon").hide();
            }
            _.each(relations.toJSON(), function(rel){
                if(latestRel == null || latestRel.startDate <= rel.startDate){
                    latestRel = rel;
                }
            });
            var name = this.model.get('name');
            if(latestRel != null){
                _.each(universities, function(uni){
                    var tmpStart = "";
                    var tmpEnd = "";
                    
                    if(start != "" && end != ""){
                        // Date already found, skip
                        return;
                    }
                    if((latestRel.endDate == '0000-00-00' && uni.end != '0000-00-00 00:00:00') ||
                       (latestRel.endDate != '0000-00-00' && uni.end != '0000-00-00 00:00:00' && uni.end < latestRel.endDate)){
                        // Relationship was not ended, but Basic Info was, use the Basic Info
                        // or Reltionship was after the Basic Info, use the Basic Info
                        tmpEnd = uni.end;
                    }
                    else{
                        // Otherwise use the relationship date
                        tmpEnd = latestRel.endDate;
                    }
                    
                    if(latestRel.startDate <= uni.start){
                        // Relationship was before Basic Info, use the Basic Info
                        tmpStart = uni.start;
                    }
                    else{
                        // Otherwise use the relationship date
                        tmpStart = latestRel.startDate;
                    }
                    
                    if(tmpStart.substr(0,10) > tmpEnd.substr(0,10) && tmpEnd.substr(0,10) != '0000-00-00'){
                        // Date doesn't make sense, so don't use it
                        console.log(tmpStart, tmpEnd, name);
                        return;
                    }
                    
                    start = tmpStart;
                    end = tmpEnd;
                    position = uni.position;
                });
            }
            
            if(start == ""){
                start = this.model.get('start');
            }
            if(end == ""){
                end = this.model.get('end');
            }
            
            if(start == '0000-00-00' || start == '0000-00-00 00:00:00' || start == '' || start == undefined){
                start = '';
            }
            if(end == '0000-00-00' || end == '0000-00-00 00:00:00' || end == '' || end == undefined){
                
                end = 'Current';
            }
            
            this.$("#relationType").text(_.uniq(relations.pluck('type')).join(", "));
            this.$("#startDate").text(start.substr(0, 10));
            this.$("#endDate").text(end.substr(0, 10));
            this.$("#status").text(relations.pluck('status').filter(function(el) { return el; }).join(", "));
            this.$("#position").html(position);
            this.parent.invalidate();
        }.bind(this));
    },
    
    render: function(){
        var classes = new Array();
        this.$("td").each(function(i, val){
            classes.push($(val).attr("class"));
        });

        // Sanity Check 1: Incomplete Student Data Check - highlights students with empty dataset
        var complete = {complete: true};
        if(_.size(_.filter([this.model.get('university'),
                            this.model.get('position'),
                            this.model.get('department')],
                            function(val){
                                return (val != undefined && 
                                        val.trim().toLowerCase() != "" && 
                                        val.trim().toLowerCase() != "unknown");
                            }
                          )
            ) != 3){
            complete.complete = false;
        }
        
        // Sanity Check 2: Relationship with Faculty Member
        var doubtful = {doubtful: false};
        if (me.get('department') != null && this.model.get('department') != null &&
            me.get('department').trim().toLowerCase() != this.model.get('department').trim().toLowerCase()){
            doubtful.doubtful = true;
        }

        this.el.innerHTML = this.template(_.extend(this.model.toJSON(), complete, doubtful));
        this.renderRelationType();
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
