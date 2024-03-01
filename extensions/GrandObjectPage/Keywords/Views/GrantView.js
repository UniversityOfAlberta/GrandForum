GrantView = Backbone.View.extend({

    person: null,
    allContributions: null,

    initialize: function(){
        this.model.fetch({
            error: function(e){
                this.$el.html("This Keyword does not exist");
            }.bind(this)
        });
        
        this.listenTo(this.model, 'change', function(){
            this.person = new Person({id: this.model.get('user_id')});
            
            this.model.getGrantAward();
            this.listenTo(this.model.grantAward, 'sync', this.render);
            if(this.person.get('id') != 0){
                var xhr = this.person.fetch();
                $.when(xhr).then(this.render);
            }
            else{
                this.render();
            }
        }.bind(this));
        
        $.get(wgServer + wgScriptPath + "/index.php?action=contributionSearch&phrase=&category=all", function(response){
            this.allContributions = response;
        }.bind(this));
        
        this.template = _.template($('#grant_template').html());
    },
    
    edit: function(){
        document.location = this.model.get('url') + "/edit";
    },
    
    delete: function(){
        if(this.model.get('deleted') != true){
            this.deleteDialog.dialog('open');
        }
        else{
            clearAllMessages();
            addError('This Keyword is already deleted');
        }
    },
    
    save: function(){
        _.defer(function(){
            this.model.save(null, {
                success: function(){
                    clearAllMessages();
                    if(this.model.get('exclude')){
                        addSuccess("The Keyword is now Excluded");
                    }
                    else{
                        addSuccess("The Keyword is no longer Excluded");
                    }
                }.bind(this),
                error: function(o, e){
                    clearAllMessages();
                    if(e.responseText != ""){
                        addError(e.responseText, true);
                    }
                    else{
                        addError("There was a problem saving the Keyword", true);
                    }
                }.bind(this)
            });
        }.bind(this));
    },
    
    events: {
        "click #edit": "edit",
        "click #delete": "delete",
        "change [name=exclude]": "save"
    },
    
    renderContributions: function(){
        if(this.allContributions != null && 
           this.allContributions.length != null && 
           this.model.get('contributions').length > 0){
            this.$("#contributions").empty();
            _.each(this.model.get('contributions'), function(cId){
                var contribution = _.findWhere(this.allContributions, {id: cId.toString()});
                this.$("#contributions").append("<li><a href='" + wgServer + wgScriptPath + "/index.php/Contribution:" + contribution.id + "'>" + contribution.name + "</a></li>");
            }.bind(this));
        }
    },
    
    renderCoPI: function(){
        var xhrs = new Array();
        var people = new Array();
        _.each(this.model.get('copi'), function(copi){
            var person = new Person({id: copi.id, realName: copi.fullname});
            people.push(person);
            xhrs.push(person.fetch());
        });
        $.when.apply($, xhrs).then(function(){
            this.$("#copi").empty();
            var html = new Array();
            _.each(people, function(copi){
                if(copi.get('id') != null){
                    html.push("<a href='" + copi.get('url') + "'>" + copi.get('realName') + "</a>");
                }
                else{
                    html.push(copi.get('realName'));
                }
            }.bind(this));
            this.$("#copi").html(html.join("; "));
        }.bind(this));
    },

    render: function(){
        main.set('title', this.model.get('title'));
        $("#pageTitle").html("<a href='#'>Keywords</a> > " + this.model.get('project_id'));
        this.$el.html(this.template(this.model.toJSON()));
        this.renderContributions();
        this.renderCoPI();
        if(this.model.get('deleted') == true){
            this.$el.find("#delete").prop('disabled', true);
            clearInfo();
            addInfo('This Keyword has been deleted, and will not show up anywhere else on the ' + siteName + '.  You may still edit the Keyword.');
        }
        this.deleteDialog = this.$("#deleteDialog").dialog({
            autoOpen: false,
            modal: true,
            show: 'fade',
            resizable: false,
            draggable: false,
            open: function(){
                $("html").css("overflow", "hidden");
                $(".ui-dialog-buttonpane button:contains('Yes')", this.deleteDialog.parent()).prop("disabled", true);
                $("#deleteCheck", this.deleteDialog).prop("checked", false);
                $("#deleteCheck", this.deleteDialog).change(function(e){
                    var isChecked = $(e.currentTarget).is(":checked");
                    if(isChecked){
                        $(".ui-dialog-buttonpane button:contains('Yes')", this.deleteDialog.parent()).prop("disabled", false);
                    }
                    else{
                        $(".ui-dialog-buttonpane button:contains('Yes')", this.deleteDialog.parent()).prop("disabled", true);
                    }
                }.bind(this));
            }.bind(this),
            beforeClose: function(){
                $("html").css("overflow", "auto");
            },
            buttons: {
                "Yes": function(){
                    var model = this.model;
                    if(model.get('deleted') != true){
                        $("div.throbber", this.deleteDialog).show();
                        model.destroy({
                            success: function(model, response) {
                                this.deleteDialog.dialog('close');
                                $("div.throbber", this.deleteDialog).hide();
                                if(response.deleted == true){
                                    model.set(response);
                                    clearSuccess();
                                    clearError();
                                    addSuccess('The Keyword <i>' + response.title + '</i> was deleted sucessfully');
                                }
                                else{
                                    clearSuccess();
                                    clearError();
                                    addError('The Keyword <i>' + response.title + '</i> was not deleted sucessfully');
                                }
                            }.bind(this),
                            error: function(model, response) {
                                clearSuccess();
                                clearError();
                                addError('The Keyword <i>' + response.title + '</i> was not deleted sucessfully');
                            }
                        });
                    }
                    else{
                        this.deleteDialog.dialog('close');
                        clearAllMessages();
                        addError('This ' + model.get('category') + ' is already deleted');
                    }
                }.bind(this),
                "No": function(){
                    this.deleteDialog.dialog('close');
                }.bind(this)
            }
        });
        return this.$el;
    }

});
