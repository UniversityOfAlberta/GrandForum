EditGrantAwardView = Backbone.View.extend({

    person: null,
    grants: null,

    initialize: function(){
        if(!this.model.isNew()){
            this.model.fetch({
                error: function(e){
                    this.$el.html("This Awarded NSERC Application does not exist");
                }.bind(this)
            });
        }
        this.grants = new Grants();
        var xhr1 = this.grants.fetch();
        this.listenTo(this.model, "change:application_title", function(){
            if(this.model.get('application_title') != ''){
                main.set('title', this.model.get('application_title'));
            }
            else if(this.model.isNew()){
                main.set('title', 'New Awarded NSERC Application');
            }
            else if(!this.model.isNew()){
                main.set('title', 'Editing Awarded NSERC Application');
            }
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
            success: function(){
                this.$(".throbber").hide();
                this.$("#save").prop('disabled', false);
                clearAllMessages();
                document.location = this.model.get('url');
            }.bind(this),
            error: function(o, e){
                this.$(".throbber").hide();
                this.$("#save").prop('disabled', false);
                clearAllMessages();
                if(e.responseText != ""){
                    addError(e.responseText, true);
                }
                else{
                    addError("There was a problem saving the Awarded NSERC Application", true);
                }
            }.bind(this)
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
    
    renderCoapplicantsWidget: function(){
        var objs = [];
        this.allPeople.each(function(p){
            objs[p.get('fullName')] = {id: p.get('id'),
                                       name: p.get('name'),
                                       fullname: p.get('fullName')};
        });
        
        var delimiter = ';';
        var html = HTML.TagIt(this, 'coapplicants.fullname', {
            values: _.pluck(this.model.get('coapplicants'), 'fullname'),
            strictValues: false, 
            objs: objs,
            options: {
                placeholderText: 'Enter applicant here...',
                allowSpaces: true,
                allowDuplicates: false,
                removeConfirmation: false,
                singleFieldDelimiter: delimiter,
                splitOn: delimiter,
                availableTags: this.allPeople.pluck('fullName'),
                afterTagAdded: function(event, ui){
                    if(this.allPeople.pluck('fullName').indexOf(ui.tagLabel) >= 0){
                        ui.tag[0].style.setProperty('background', highlightColor, 'important');
                        ui.tag.children("a").children("span")[0].style.setProperty("color", "white", 'important');
                        ui.tag.children("span")[0].style.setProperty("color", "white", 'important');
                    }
                }.bind(this),
                tagSource: function(search, showChoices) {
                    if(search.term.length < 2){ showChoices(); return; }
                    var filter = search.term.toLowerCase();
                    var choices = $.grep(this.options.availableTags, function(element) {
                        return (element.toLowerCase().match(filter) !== null);
                    });
                    showChoices(this._subtractArray(choices, this.assignedTags()));
                }
            }
        });
        this.$("#coapplicants").html(html);
        this.$("#coapplicants").append("<p><i>Drag to re-order each applicant</i></p>");
        this.$("#coapplicants .tagit").sortable({
            stop: function(event,ui) {
                $('input[name=coapplicants_fullname]').val(
                    $(".tagit-label",$(this))
                        .clone()
                        .text(function(index,text){ return (index == 0) ? text : delimiter + text; })
                        .text()
                ).change();
            }
        });
        this.$el.on('mouseover', 'div[name=coapplicants_fullname] li.tagit-choice', function(){
            $(this).css('cursor', 'move');
        });
    },
    
    renderCoapplicants: function(){
        if(this.allPeople != null && this.allPeople.length > 0){
            this.renderCoapplicantsWidget();
        }
        else{
            this.allPeople = new People();
            this.allPeople.simple = true;
            this.allPeople.fetch();
            var spin = spinner("coapplicants", 10, 20, 10, 3, '#888');
            this.allPeople.bind('sync', function(){
                if(this.allPeople.length > 0){
                    this.renderCoapplicantsWidget();
                }
            }, this);
        }
    },
    
    renderPartners: function(){
        this.$("#partners").empty();
        _.each(this.model.get('partners'), function(partner){
            var view = new EditPartnerView({model: partner, parent: this});
            this.$("#partners").append(view.render());
        }.bind(this));
    },
    
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        this.renderCoapplicants();
        this.renderPartners();
        this.$('input[name=amount]').forceNumeric({min: 0, max: 100000000000,includeCommas: false, decimals: 2});
        this.$('input[name=fiscal_year]').forceNumeric({min: 0, max: 9999,includeCommas: false});
        this.$('input[name=competition_year]').forceNumeric({min: 0, max: 9999,includeCommas: false});
        this.$('select[name=grant_id]').chosen();
        this.$('select[name=area_of_application_group]').chosen();
        this.$('select[name=area_of_application]').chosen({width: "400px"});
        this.$('select[name=research_subject_group]').chosen({width: "400px"});
        return this.$el;
    }

});
