GrantAwardView = Backbone.View.extend({

    person: null,
    allContributions: null,

    initialize: function(){
        this.model.fetch({
            error: $.proxy(function(e){
                this.$el.html("This Awarded NSERC Application does not exist");
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
    
    renderCoApplicants: function(){
        var views = Array();
        var that = this;
        _.each(this.model.get('coapplicants'), function(author, index){
            var link = new Link({id: author.id,
                                 text: author.name.replace(/&quot;/g, ''),
                                 url: author.url,
                                 target: ''});
            views.push(new PersonLinkView({model: link}).render());
        });
        var csv = new CSVView({el: this.$('#coapplicants'), model: views});
        csv.separator = '; ';
        csv.render();
    },

    render: function(){
        main.set('title', this.model.get('application_title'));
        this.$el.html(this.template(this.model.toJSON()));
        this.renderCoApplicants();
        return this.$el;
    }

});
