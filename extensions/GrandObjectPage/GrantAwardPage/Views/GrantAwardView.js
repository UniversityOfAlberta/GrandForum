GrantAwardView = Backbone.View.extend({

    person: null,
    allContributions: null,

    initialize: function(){
        this.model.fetch({
            error: $.proxy(function(e){
                this.$el.html("This Grant Award does not exist");
            }, this)
        });
        
        this.listenTo(this.model, 'change', $.proxy(function(){
            this.person = new Person({id: this.model.get('user_id')});
            var xhr = this.person.fetch();
            this.listenTo(this.model.grant, 'sync', this.render);
            this.model.getGrant();
            $.when(xhr).then(this.render);
        }, this));
        
        this.template = _.template($('#grantaward_template').html());
    },
    
    edit: function(){
        document.location = this.model.get('url') + "/edit";
    },
    
    events: {
        "click #edit": "edit"
    },

    render: function(){
        main.set('title', this.model.get('application_title'));
        this.$el.html(this.template(this.model.toJSON()));
        return this.$el;
    }

});
