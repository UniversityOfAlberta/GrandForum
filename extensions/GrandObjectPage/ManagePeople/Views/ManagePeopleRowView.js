ManagePeopleRowView = Backbone.View.extend({
    
    tagName: 'tr',
    parent: null,
    row: null,
    // Views
    editRoles: null,
    editUniversities: null,
    // Dialogs
    rolesDialog: null,
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
    
    initializeUniversitiesDialog: function(){
        if(this.universitiesDialog == null){
            this.universitiesDialog = this.$("#universitiesDialog").dialog({
                autoOpen: false,
                modal: true,
                show: 'fade',
                resizable: false,
                draggable: false,
                width: '1100px',
                position: {
                    my: "center bottom",
                    at: "center center"
                },
                open: function(){
                    $("html").css("overflow", "hidden");
                },
                beforeClose: function(){
                    $("html").css("overflow", "auto");
                    this.editUniversities.clean();
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
        "click #editUniversities": "openUniversitiesDialog",
        "click .delete-icon": "removePerson"
    },
    
    renderRelationType: function(){
        if(!me.relations.fetching()){ // Only Fetch once
            me.getRelations();
        }
        me.relations.ready().then(function(){
            var universities = this.model.get('universities');
            var uniIds = _.pluck(universities, 'id');
            var relations = new PersonRelations(me.relations.where({user2: this.model.get('id')}));
            var start = ""; this.model.get('start');
            var end = ""; this.model.get('end');
            var position = this.model.get('position');
            
            // Sanity Check 3: Contains Orphaned relationships
            var foundOrphan = false;
            relations.each(function(relation){
                if(relation.get('university') == 0 || uniIds.indexOf(relation.get('university')) === false){
                    // Orphan found
                    foundOrphan = true;
                }
            }.bind(this));
            if(foundOrphan){
                this.$(".hqpError ul").append("<li>HQP has Orphaned Relationships</li>");
            }
            
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
                    
                    if(tmpStart.substr(0,10) > tmpEnd.substr(0,10) && tmpEnd.substr(0,10) != '0000-00-00'){
                        // Date doesn't make sense, so don't use it
                        return;
                    }
                    
                    start = latestRel.startDate;
                    end = latestRel.endDate;
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
        this.el.innerHTML = this.template(this.model.toJSON());
        
        // Sanity Check 1: Incomplete Student Data Check - highlights students with empty dataset
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
            this.$(".hqpError ul").append("<li>Basic Info is incomplete</li>");
        }
        
        // Sanity Check 2: Department different than Faculty Member
        if (me.get('department') != null && this.model.get('department') != null &&
            me.get('department').trim().toLowerCase() != this.model.get('department').trim().toLowerCase()){
            this.$(".hqpError ul").append("<li>This HQP may not be your student</li>");
        }
                
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
