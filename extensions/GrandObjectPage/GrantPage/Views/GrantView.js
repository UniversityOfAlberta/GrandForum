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
    
    events: {
        "click #edit": "edit"
    },
    
    renderContributions: function(){
        if(this.allContributions.length != null && this.model.get('contributions').length > 0){
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
            var person = new Person({id: copi});
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
        return this.$el;
    }

});
