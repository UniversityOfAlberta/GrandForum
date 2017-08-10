EditGrantAwardView = Backbone.View.extend({

    person: null,

    initialize: function(){
        if(!this.model.isNew()){
            this.model.fetch({
                error: $.proxy(function(e){
                    this.$el.html("This Grant Award does not exist");
                }, this)
            });
        }
        this.listenTo(this.model, "change:application_title", function(){
            main.set('title', this.model.get('application_title'));
        });
        this.listenTo(this.model, 'sync', function(){
            this.person = new Person({id: this.model.get('user_id')});
            var xhr = this.person.fetch();
            $.when(xhr).then(this.render);
        });
        
        this.template = _.template($('#edit_grantaward_template').html());
        if(this.model.isNew()){
            this.model.trigger("sync");
        }
    },
    
    save: function(){
        this.$(".throbber").show();
        this.$("#save").prop('disabled', true);
        this.model.save(null, {
            success: $.proxy(function(){
                this.$(".throbber").hide();
                this.$("#save").prop('disabled', false);
                clearAllMessages();
                document.location = this.model.get('url');
            }, this),
            error: $.proxy(function(o, e){
                this.$(".throbber").hide();
                this.$("#save").prop('disabled', false);
                clearAllMessages();
                if(e.responseText != ""){
                    addError(e.responseText, true);
                }
                else{
                    addError("There was a problem saving the Grant Award", true);
                }
            }, this)
        });
    },
    
    addPartner: function(){
        this.model.get('partners').push(new GrantPartner({award_id: this.model.get('id')})); 
        this.renderPartners();
    },
    
    events: {
        "click #save": "save",
        "click #addPartner": "addPartner"
    },
    
    renderPartners: function(){
        this.$("#partners").empty();
        _.each(this.model.get('partners'), $.proxy(function(partner){
            var view = new EditPartnerView({model: partner, parent: this});
            this.$("#partners").append(view.render());
        }, this));
    },
    
    render: function(){
        main.set('title', this.model.get('application_title'));
        this.$el.html(this.template(this.model.toJSON()));
        this.renderPartners();
        this.$('input[name=amount]').forceNumeric({min: 0, max: 100000000000,includeCommas: true, decimals: 2});
        this.$('input[name=fiscal_year]').forceNumeric({min: 0, max: 9999,includeCommas: false});
        this.$('input[name=competition_year]').forceNumeric({min: 0, max: 9999,includeCommas: false});
        return this.$el;
    }

});
