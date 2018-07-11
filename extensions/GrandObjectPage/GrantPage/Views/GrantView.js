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
            this.model.destroy({
                success: function(model, response) {
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
                },
                error: function(model, response) {
                    clearSuccess();
                    clearError();
                    addError('The Revenue Account <i>' + response.title + '</i> was not deleted sucessfully');
                }
            });
        }
        else{
            clearAllMessages();
            addError('This Revenue Account is already deleted');
        }
    },
    
    events: {
        "click #edit": "edit",
        "click #delete": "delete"
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
            var person = new Person({id: copi.id});
            people.push(person);
            xhrs.push(person.fetch());
        });
        $.when.apply($, xhrs).then($.proxy(function(){
            this.$("#copi").empty();
            var html = new Array();
            _.each(people, $.proxy(function(copi){
                html.push("<a href='" + copi.get('url') + "'>" + copi.get('realName') + "</a>");
            }, this));
            this.$("#copi").html(html.join("; "));
        }, this));
    },

    render: function(){
        main.set('title', this.model.get('title'));
        this.$el.html(this.template(this.model.toJSON()));
        this.renderContributions();
        this.renderCoPI();
        if(this.model.get('deleted') == true){
            this.$el.find("#delete").prop('disabled', true);
            clearInfo();
            addInfo('This Revenue Account has been deleted, and will not show up anywhere else on the ' + siteName + '.  You may still edit the Revenue Account.');
        }
        return this.$el;
    }

});
