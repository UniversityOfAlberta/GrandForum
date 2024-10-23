ManagePeopleEditUniversitiesView = Backbone.View.extend({

    universities: null,
    person: null,
    universityViews: null,
    editRelations: null,
    interval: null,

    initialize: function(options){
        this.person = options.person;
        this.model.fetch();
        me.relations.fetch();
        this.template = _.template($('#edit_universities_template').html());
        this.universityViews = new Array();
        
        this.model.ready().then(function(){
            this.universities = this.model;
            this.listenTo(this.universities, "add", this.addRows);
            this.universities.each(function(u){
                u.startTracking();
            });
            this.render();
        }.bind(this));
        
        // Reposition the dialog when the window is resized or the dialog is resized
        var dim = {w1: 0,
                   h1: 0,
                   w2: 0,
                   h2: 0};
        this.interval = setInterval(function(){
            if(this.$el.hasClass('ui-dialog-content')){
                if(this.$el.width() != dim.w1 ||
                   this.$el.height() != dim.h1 ||
                   $(window).width() != dim.w2 ||
                   $(window).height() != dim.h2){
                    if(this.$el.height() >= $(window).height() - 100){
                        this.$el.height($(window).height() - 100);
                    }
                    else{
                        this.$el.height('auto');
                    }
                    this.$el.dialog("option","position", {
                        my: "center center",
                        at: "center center"
                    });
                }
                dim.w1 = this.$el.width();
                dim.h1 = this.$el.height();
                dim.w2 = $(window).width();
                dim.h2 = $(window).height();
            }
        }.bind(this), 100);
    },
    
    clean: function(){
        _.each(this.universityViews, function(uniView){
            if(uniView.editRelations != null){
                uniView.editRelations.clean();
                
                uniView.stopListening();
                uniView.undelegateEvents();
            }
        });
        if(this.editRelations != null){
            this.editRelations.clean();
        }
        this.stopListening();
        this.undelegateEvents();
    },
    
    saveAll: function(refresh){
        var refresh = (refresh === undefined) ? true : refresh;
        var copy = this.universities.toArray();
        clearAllMessages();
        var requests = new Array();
        _.each(copy, function(university){
            if(university.unsavedAttributes() != false){
                if(university.get('deleted') != "true"){
                    var xhr = university.save(null);
                    if(xhr == false){
                        addError(university.validationError);
                    }
                    else{
                        requests.push(xhr);
                    }
                }
                else {
                    requests.push(university.destroy({wait: true}));
                }
            }
        }.bind(this));
        $.when.apply($, requests).then(function(){
            if(this.person.get('id') != me.get('id')){
                // Save Relations
                _.each(this.universityViews, function(view){
                    view.editRelations.saveAll();
                });
                this.editRelations.saveAll();
                
                // After saving, disassociate relations
                _.each(this.universityViews, function(view){
                    view.editRelations.disassociate();
                });
            }
            addSuccess("Universities saved");
            if(refresh){
                this.person.fetch();
            }
        }.bind(this)).fail(function(e){
            // Save Relations
            _.each(this.universityViews, function(view){
                view.editRelations.saveAll();
            });
            this.editRelations.saveAll();
            
            // After saving, disassociate relations
            _.each(this.universityViews, function(view){
                view.editRelations.disassociate();
            });
            
            addError(e.responseText);
            
            if(refresh){
                this.person.fetch();
            }
        }.bind(this));
        return requests;
    },
    
    addUniversity: function(){
        var university = new PersonUniversity();
        university.startTracking();
        university.set("university", "Unknown");
        university.set("department", "Unknown");
        university.set("position", "Unknown");
        university.set("personId", this.person.get('id'));
        this.universities.add(university);
        this.$el.scrollTop(this.el.scrollHeight);
    },
    
    addRows: function(){
        this.universities.each(function(university, i){
            if(this.universityViews[i] == null){
                var view = new ManagePeopleEditUniversitiesRowView({model: university, person: this.person});
                this.$("#university_table").append(view.render());
                if(i % 2 == 0){
                    view.$el.addClass('even');
                }
                else{
                    view.$el.addClass('odd');
                }
                this.universityViews[i] = view;
            }
        }.bind(this));
    },
    
    render: function(){
        this.$el.empty();
        this.$el.html(this.template());
        this.addRows();
        if(this.person.get('id') != me.get('id')){
            this.editRelations = new ManagePeopleEditRelationsView({model: me.relations,
                                                                    parent: this,
                                                                    person: this.person, 
                                                                    university: null,
                                                                    el: this.$("#orphans")});
        }
        return this.$el;
    }

});

ManagePeopleEditUniversitiesRowView = Backbone.View.extend({
    
    tagName: 'tbody',
    person: null,
    editRelations: null,
    
    initialize: function(options){
        this.person = options.person;
        this.listenTo(this.model, "change", this.update);
        this.template = _.template($('#edit_universities_row_template').html());
    },
    
    // Sets the end date to infinite (0000-00-00)
    setInfinite: function(){
        this.$("#uniEnd input[name=endDate]").val('').change();
        this.model.set('endDate', '');
    },
    
    events: {
        "click #uniEnd #infinity": "setInfinite",
        "click #addRelation": function(){
            this.editRelations.addRelation();
        },
        "change #uniStart [name=startDate]": "changeStart",
        "change #uniEnd [name=endDate]": "changeEnd"
    },
    
    changeStart: function(){
        // These probably won't exist in most cases, but if they do, then yay
        var start_date = this.$("#uniStart [name=startDate]").val();
        var end_date = this.$("#uniEnd [name=endDate]").val();
        if(start_date != "" && start_date != "0000-00-00"){
            this.$("#uniEnd [name=endDate]").datepicker("option", "minDate", start_date);
        }
    },
    
    changeEnd: function(){
        // These probably won't exist in most cases, but if they do, then yay
        var start_date = this.$("#uniStart [name=startDate]").val();
        var end_date = this.$("#uniEnd [name=endDate]").val();
        if(end_date != "" && end_date != "0000-00-00"){
            this.$("#uniStart [name=startDate]").datepicker("option", "maxDate", end_date);
        }
        else{
            this.$("#uniStart [name=startDate]").datepicker("option", "maxDate", null);
        }
    },
    
    update: function(){
        if(this.model.get('deleted') == "true"){
            this.$("> tr").addClass('deleted');
            if(this.editRelations != null){
                _.each(this.editRelations.relationViews, function(view){
                    view.model.set('deleted', "true");
                    view.render();
                });
            }
        }
        else{
            this.$("> tr").removeClass('deleted');
            if(this.editRelations != null){
                _.each(this.editRelations.relationViews, function(view){
                    view.model.set('deleted', "false");
                    view.render();
                });
            }
        }
    },
   
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        this.$("[name=university]").css('max-width', '200px').css('width', '200px');
        this.$("[name=department]").css('max-width', '200px').css('width', '200px');
        this.$("[name=researchArea]").css('max-width', '200px').css('width', '200px');
        this.$("[name=position]").css('max-width', '245px').css('width', '245px');
        this.$("[name=university]").combobox();
        this.$("[name=department]").combobox();
        this.$("[name=researchArea]").combobox();
        if(!(_.where(this.person.get('roles'), {role: HQP}).length > 0 && 
             _.filter(this.person.get('roles'), function(r){ return !(r.role == HQP); }).length == 0)){
            this.$("[name=position]").css('max-width', '200px').css('width', '200px');
            this.$("[name=position]").combobox();
        }
        this.update();
        _.defer(function(){
            this.$("#uniStart [name=startDate]").change();
            this.$("#uniEnd [name=endDate]").change();
        }.bind(this));
        if(this.person.get('id') != me.get('id')){
            this.editRelations = new ManagePeopleEditRelationsView({model: me.relations, 
                                                                    parent: this,
                                                                    person: this.person, 
                                                                    university: this.model,
                                                                    el: this.$(".relations")});
        }
        return this.$el;
    }, 
    
});
