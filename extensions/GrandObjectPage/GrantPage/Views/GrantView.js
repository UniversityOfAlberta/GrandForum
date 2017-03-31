GrantView = Backbone.View.extend({

    person: null,
    allContributions: null,

    initialize: function(){
        this.model.fetch({
            error: $.proxy(function(e){
                this.$el.html("This Grant does not exist");
            }, this)
        });
        this.listenTo(this.model, 'change', $.proxy(function(){
            this.person = new Person({id: this.model.get('user_id')});
            var xhr = this.person.fetch();
            $.when(xhr).then(this.render);
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

    render: function(){
        main.set('title', this.model.get('title'));
        this.$el.html(this.template(this.model.toJSON()));
        this.renderContributions();
        return this.$el;
    }

});
