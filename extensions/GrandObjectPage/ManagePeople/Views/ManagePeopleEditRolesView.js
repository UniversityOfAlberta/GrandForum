ManagePeopleEditRolesView = Backbone.View.extend({

    roles: null,
    person: null,

    initialize: function(options){
        this.person = options.person;
        this.model.fetch();
        this.listenTo(this.model, "change", this.render);
        this.template = _.template($('#edit_roles_template').html());
        this.model.ready().then($.proxy(function(){
            this.roles = this.model.getAll();
            this.listenTo(this.roles, "add", this.addRows);
            this.model.ready().then($.proxy(function(){
                this.render();
            }, this));
        }, this));
    },
    
    saveAll: function(){
        var copy = this.roles.toArray();
        _.each(copy, $.proxy(function(role){
            if(role.get('deleted') != "true"){
                role.save();
            }
            else {
                role.destroy();
            }
        }, this));
    },
    
    addRole: function(){
        this.roles.add(new Role({name: "HQP", userId: this.person.get('id')}));
    },
    
    addRows: function(){
        if(this.roles.length > 0){
            this.$("#role_rows").empty();
        }
        this.roles.each($.proxy(function(role){
            var view = new ManagePeopleEditRolesRowView({model: role});
            this.$("#role_rows").append(view.render());
        }, this));
    },
    
    events: {
        "click #add": "addRole"
    },
    
    render: function(){
        this.$el.empty();
        this.$el.html(this.template());
        this.addRows();
        return this.$el;
    }

});

ManagePeopleEditRolesRowView = Backbone.View.extend({
    
    tagName: 'tr',
    
    initialize: function(){
        this.listenTo(this.model, "change", this.update);
        this.template = _.template($('#edit_roles_row_template').html());
    },
    
    delete: function(){
        this.model.delete = true;
    },
    
    events: {},
    
    update: function(){
        if(this.model.get('deleted') == "true"){
            this.$el.css('background', '#FEB8B8');
        }
        else{
            this.$el.css('background', '#FFFFFF');
        }
    },
   
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        return this.$el;
    }, 
    
});
