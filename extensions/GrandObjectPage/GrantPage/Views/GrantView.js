GrantView = Backbone.View.extend({

    person: null,
    allContributions: null,

    initialize: function(){
        this.model.fetch({
            error: $.proxy(function(e){
                this.$el.html("This Revenue Account does not exist");
            }, this)
        });
        
        this.listenTo(this.model, 'change', $.proxy(function(){
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
        }, this));
        
        $.get(wgServer + wgScriptPath + "/index.php?action=contributionSearch&phrase=&category=all", $.proxy(function(response){
            this.allContributions = response;
        }, this));
        
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
            addError('This Revenue Account is already deleted');
        }
    },
    
    save: function(){
        _.defer($.proxy(function(){
            this.model.save(null, {
                success: $.proxy(function(){
                    clearAllMessages();
                    if(this.model.get('exclude')){
                        addSuccess("The Revenue Account is now Excluded");
                    }
                    else{
                        addSuccess("The Revenue Account is no longer Excluded");
                    }
                }, this),
                error: $.proxy(function(o, e){
                    clearAllMessages();
                    if(e.responseText != ""){
                        addError(e.responseText, true);
                    }
                    else{
                        addError("There was a problem saving the Revenue Account", true);
                    }
                }, this)
            });
        }, this));
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
            _.each(this.model.get('contributions'), $.proxy(function(cId){
                var contribution = _.findWhere(this.allContributions, {id: cId.toString()});
                this.$("#contributions").append("<li><a href='" + wgServer + wgScriptPath + "/index.php/Contribution:" + contribution.id + "'>" + contribution.name + "</a></li>");
            }, this));
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
        $.when.apply($, xhrs).then($.proxy(function(){
            this.$("#copi").empty();
            var html = new Array();
            _.each(people, $.proxy(function(copi){
                if(copi.get('id') != null){
                    html.push("<a href='" + copi.get('url') + "'>" + copi.get('realName') + "</a>");
                }
                else{
                    html.push(copi.get('realName'));
                }
            }, this));
            this.$("#copi").html(html.join("; "));
        }, this));
    },

    render: function(){
        main.set('title', this.model.get('title'));
        $("#pageTitle").html("<a href='#'>Revenue Accounts</a> > " + this.model.get('title'));
        this.$el.html(this.template(this.model.toJSON()));
        this.renderContributions();
        this.renderCoPI();
        if(this.model.get('deleted') == true){
            this.$el.find("#delete").prop('disabled', true);
            clearInfo();
            addInfo('This Revenue Account has been deleted, and will not show up anywhere else on the ' + siteName + '.  You may still edit the Revenue Account.');
        }
        this.deleteDialog = this.$("#deleteDialog").dialog({
            autoOpen: false,
            modal: true,
            show: 'fade',
            resizable: false,
            draggable: false,
            open: $.proxy(function(){
                $("html").css("overflow", "hidden");
                $(".ui-dialog-buttonpane button:contains('Yes')", this.deleteDialog.parent()).prop("disabled", true);
                $("#deleteCheck", this.deleteDialog).prop("checked", false);
                $("#deleteCheck", this.deleteDialog).change($.proxy(function(e){
                    var isChecked = $(e.currentTarget).is(":checked");
                    if(isChecked){
                        $(".ui-dialog-buttonpane button:contains('Yes')", this.deleteDialog.parent()).prop("disabled", false);
                    }
                    else{
                        $(".ui-dialog-buttonpane button:contains('Yes')", this.deleteDialog.parent()).prop("disabled", true);
                    }
                }, this));
            }, this),
            beforeClose: function(){
                $("html").css("overflow", "auto");
            },
            buttons: {
                "Yes": $.proxy(function(){
                    var model = this.model;
                    if(model.get('deleted') != true){
                        $("div.throbber", this.deleteDialog).show();
                        model.destroy({
                            success: $.proxy(function(model, response) {
                                this.deleteDialog.dialog('close');
                                $("div.throbber", this.deleteDialog).hide();
                                if(response.deleted == true){
                                    model.set(response);
                                    clearSuccess();
                                    clearError();
                                    addSuccess('The Revenue Account <i>' + response.title + '</i> was deleted sucessfully');
                                }
                                else{
                                    clearSuccess();
                                    clearError();
                                    addError('The Revenue Account <i>' + response.title + '</i> was not deleted sucessfully');
                                }
                            }, this),
                            error: function(model, response) {
                                clearSuccess();
                                clearError();
                                addError('The Revenue Account <i>' + response.title + '</i> was not deleted sucessfully');
                            }
                        });
                    }
                    else{
                        this.deleteDialog.dialog('close');
                        clearAllMessages();
                        addError('This ' + model.get('category') + ' is already deleted');
                    }
                }, this),
                "No": $.proxy(function(){
                    this.deleteDialog.dialog('close');
                }, this)
            }
        });
        return this.$el;
    }

});
