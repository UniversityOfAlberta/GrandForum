ManagePeopleEditRolesView = Backbone.View.extend({

    roles: null,

    initialize: function(){
        this.model.fetch();
        this.listenTo(this.model, "change", this.render);
        this.template = _.template($('#edit_roles_template').html());
        this.model.ready().then($.proxy(function(){
            this.roles = this.model.getAll();
            this.model.ready().then($.proxy(function(){
                this.addRows();
            }, this));
        }, this));
        this.render();
    },
    
    addRows: function(){
        this.$("#role_rows").empty();
        this.roles.each(function(role){
            var view = new ManagePeopleEditRolesRowView({model: role});
            this.$("#role_rows").append(view.render());
        });
    },
    
    render: function(){
        this.$el.html(this.template());
        return this.$el;
    }

});

ManagePeopleEditRolesRowView = Backbone.View.extend({
    
    tagName: 'tr',
    
    initialize: function(){
        this.template = _.template($('#edit_roles_row_template').html());
    },
    
    events: {},
   
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        return this.$el;
    }, 
    
});
