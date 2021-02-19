ContributionEditView = Backbone.View.extend({

    initialize: function(options){
        this.parent = this;
        this.listenTo(this.model, "sync", this.render);
        this.listenTo(this.model, "change:total", this.renderTotal);
        this.listenTo(this.model, "add:partners", this.render);
        this.listenTo(this.model, "delete:partners", this.render);
        
        this.listenTo(this.model, "change:name", function(){
            if(!this.isDialog){
                main.set('title', this.model.get('name'));
            }
        });
        this.template = _.template($('#contribution_edit_template').html());
        
        if(!this.model.isNew() && !this.isDialog){
            this.model.fetch({silent: true, 
                              success: function(){
                                this.listenTo(this.model, "change:partners", this.renderTotal);
                              }.bind(this)
                             });
        }
        else{
            _.defer(this.render);
        }
    },
    
    events: {
        "click #saveContribution": "saveContribution",
        "click #cancel": "cancel",
        "click button#addPartner": "addPartner",
        "click button.deletePartner": "deletePartner",
        "change .partner_name": function(){
            _.defer(this.renderPartners.bind(this));
        },
        "change .partner_type": function(){
            _.defer(this.renderPartners.bind(this));
        },
    },
    
    deletePartner: function(e){
        var el = $(e.target);
        var id = el.attr('data-id');
        var partners = this.model.get('partners');
        partners.splice(id, 1);
        this.model.set('partners', _.clone(partners));
        this.model.trigger('delete:partners');
    },
    
    addPartner: function(){
        this.model.addPartner();
    },
    
    validate: function(){
        if(this.model.get('name').trim() == ""){
            return "The Contribution must have a title";
        }
        return "";
    },
    
    saveContribution: function(){
        var validation = this.validate();
        if(validation != ""){
            clearAllMessages();
            addError(validation, true);
            return;
        }
        this.$(".throbber").show();
        this.$("#saveContribution").prop('disabled', true);
        this.model.save(null, {
            success: function(){
                this.$(".throbber").hide();
                this.$("#saveContribution").prop('disabled', false);
                clearAllMessages();
                document.location = this.model.get('url');
            }.bind(this),
            error: function(o, e){
                this.$(".throbber").hide();
                this.$("#saveContribution").prop('disabled', false);
                clearAllMessages();
                if(e.responseText != ""){
                    addError(e.responseText, true);
                }
                else{
                    addError("There was a problem saving the Contribution", true);
                }
            }.bind(this)
        });
    },
    
    cancel: function(){
        document.location = this.model.get('url');
    },
    
    renderAuthorsWidget: function(){
        var left = _.pluck(this.model.get('authors'), 'fullname');
        var right = _.difference(this.allPeople.pluck('fullName'), left);
        var objs = [];
        this.allPeople.each(function(p){
            objs[p.get('fullName')] = {id: p.get('id'),
                                       name: p.get('name'),
                                       fullname: p.get('fullName')};
        });
        var html = HTML.Switcheroo(this, 'authors.fullname', {name: 'person',
                                                          'left': left,
                                                          'right': right,
                                                          'objs': objs
                                                          });
        this.$("#contributionAuthors").html(html);
        createSwitcheroos();
    },
    
    renderAuthors: function(){
        if($("#contributionAuthors").length > 0){
            if(this.allPeople != null && this.allPeople.length > 0){
                this.renderAuthorsWidget();
            }
            else{
                this.allPeople = new People();
                this.allPeople.fetch();
                var spin = spinner("contributionAuthors", 10, 20, 10, 3, '#888');
                this.allPeople.bind('sync', function(){
                    if(this.allPeople.length > 0){
                        this.renderAuthorsWidget();
                    }
                }, this);
            }
        }
    },
    
    renderPartners: function(){
        this.$("#saveContribution").prop('disabled', false);
        _.each(this.model.get('partners'), function(partner, i){
            var lastType = this.$("#partner" + i).attr('last-type');
            var type = partner.type;
            this.$("#partner" + i).attr('last-type', type);
            if(lastType != type){
                // Changed UI based on Type
                if(type == 'In-Kind'){
                    this.$("#partner" + i + " #amount").hide();
                    this.$("#partner" + i + " #inkind").show();
                    this.$("#partner" + i + " #cash").hide();
                }
                else if(type == 'Cash and In-Kind'){
                    this.$("#partner" + i + " #amount").hide();
                    this.$("#partner" + i + " #inkind").show();
                    this.$("#partner" + i + " #cash").show();
                }
                else if(type == 'Cash'){
                    this.$("#partner" + i + " #amount").hide();
                    this.$("#partner" + i + " #inkind").hide();
                    this.$("#partner" + i + " #cash").show();
                }
                else if(type != ""){
                    this.$("#partner" + i + " #amount").show();
                    this.$("#partner" + i + " #inkind").hide();
                    this.$("#partner" + i + " #cash").hide();
                }
                else{
                    this.$("#partner" + i + " #amount").hide();
                    this.$("#partner" + i + " #inkind").hide();
                    this.$("#partner" + i + " #cash").hide();
                }
                this.$("#partner" + i + " tr:hidden input.amount").each(function(id, el){
                    if($(el).val() != ""){
                        $(el).val("").change();
                    }
                }.bind(this));
            }
            
            // Warnings
            this.$("#warning" + i).empty();
            var reg = /^\d*$/;
            if(partner.name.trim() == ''){
                this.$("#warning" + i).append("This partner is missing a name<br />");
            }
            if(partner.type == ""){
                this.$("#warning" + i).append("This partner is missing a contribution type<br />");
            }
            if(this.$("#warning" + i).text().trim() != ""){
                this.$("#warning" + i).show();
                this.$("#saveContribution").prop('disabled', true);
            }
            else{
                this.$("#warning" + i).hide();
            }
        }.bind(this));
        if(networkName == "MtS"){
            this.$("select.partner_type option").not(":contains(Cash)").not(":contains(In-Kind)").not(":contains(Select)").remove();
        }
        this.$(".amount").forceNumeric({min: 0, max: Number.MAX_VALUE});
        this.renderTotal();
    },
    
    renderTotal: function(){
        this.$("#contributionTotal").text(number_format(this.model.get('total')));
    },
    
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        this.renderAuthors();
        this.renderPartners();
        return this.$el;
    }

});
