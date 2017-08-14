EditGrantAwardView = Backbone.View.extend({

    person: null,
    grants: null,

    initialize: function(){
        if(!this.model.isNew()){
            this.model.fetch({
                error: $.proxy(function(e){
                    this.$el.html("This Grant Award does not exist");
                }, this)
            });
        }
        this.grants = new Grants();
        var xhr1 = this.grants.fetch();
        this.listenTo(this.model, "change:application_title", function(){
            main.set('title', this.model.get('application_title'));
        });
        this.listenTo(this.model, 'sync', function(){
            if(this.model.get('grant_id') == 0){
                this.model.set('grant_id', '');
            }
            this.person = new Person({id: this.model.get('user_id')});
            var xhr2 = this.person.fetch();
            $.when.apply($, [xhr1, xhr2]).then(this.render);
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
        this.$('select[name=grant_id]').chosen();
        this.$('select[name=area_of_application_group]').chosen();
        this.$('select[name=area_of_application]').chosen({width: "400px"});
        this.$('select[name=research_subject_group]').chosen({width: "400px"});
        return this.$el;
    }

});
