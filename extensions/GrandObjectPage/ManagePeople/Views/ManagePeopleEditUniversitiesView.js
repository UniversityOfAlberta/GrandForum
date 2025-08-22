ManagePeopleEditUniversitiesView = Backbone.View.extend({

    universities: null,
    person: null,
    universityViews: null,
    interval: null,

    initialize: function(options){
        this.person = options.person;
        this.person.getRoles();
        this.model.fetch();
        this.template = _.template($('#edit_universities_template').html());
        this.universityViews = new Array();
    
        this.model.ready().then(function(){
            this.person.roles.ready().then(function(){
                this.universities = this.model;
                this.listenTo(this.universities, "add", this.addRows);
                this.universities.each(function(u){
                    u.startTracking();
                });
                this.render();
            }.bind(this));
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
	    Backbone.Subviews.add(this);
    },
    
    subviewCreators: {
        "levelOfStudy" : function() {
            return new ManagePeopleEditLevelOfStudyView({parent: this, model: this.person});
        }
    },
    
    saveAll: function(){
        var copy = this.universities.toArray();
        clearAllMessages();
        var requests = new Array();
        _.each(copy, function(university){
            if(university.unsavedAttributes() != false){
                if(university.get('deleted') != "true"){
                    requests.push(university.save(null));
                }
                else {
                    requests.push(university.destroy(null));
                }
            }
        }.bind(this));
        if(this.person.unsavedAttributes() != false){
            requests.push(this.person.save());
        }
        $.when.apply($, requests).then(function(){
            addSuccess("Institutions saved");
        }).fail(function(){
            addError("Institutions could not be saved");
        });
        return requests;
    },
    
    addUniversity: function(){
        var university = new PersonUniversity();
        university.startTracking();
        university.set("university", "Unknown");
        university.set("department", "Unknown");
        university.set("position", "");
        university.set("personId", this.person.get('id'));
        this.universities.add(university);
        this.$el.scrollTop(this.el.scrollHeight);
    },
    
    addRows: function(){
        this.universities.each(function(university, i){
            if(this.universityViews[i] == null){
                var view = new ManagePeopleEditUniversitiesRowView({model: university, person: this.person});
                this.$("#university_rows").append(view.render());
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
        return this.$el;
    }

});

// View for editing a University row
ManagePeopleEditUniversitiesRowView = Backbone.View.extend({
    
    tagName: 'tr',
    person: null,
    
    initialize: function(options){
        this.person = options.person;
        this.model.set('deleted', false);
        this.listenTo(this.model, "change", this.update);
        this.template = _.template($('#edit_universities_row_template').html());
    },
    
    // Sets the end date to infinite (0000-00-00)
    setInfinite: function(){
        this.$("input[name=endDate]").val('0000-00-00').trigger("change");
        this.model.set('endDate', '0000-00-00');
    },
    
    events: {
        "click #infinity": "setInfinite"
    },
    
    update: function(){
        if(this.model.get('deleted') == "true"){
            this.$el.addClass('deleted');
        }
        else{
            this.$el.removeClass('deleted');
        }
        if((this.model.get('startDate') > this.model.get('endDate')) && this.model.get('endDate').substr(0,10) != "0000-00-00" && this.model.get('endDate') != ""){
            this.$(".endDateCell").css("background", "#FDEEB2")
                                  .css("box-shadow", "inset 0 0 0 1px #9C600D");
            this.$(".endDateCell .projError").text("The end date should not be before the start date").show();
        }
        else{
            this.$(".endDateCell").css("background", "")
                                  .css("box-shadow", "");
            this.$(".endDateCell .projError").text("").hide();
        }
    },
   
    render: function(){
        var date = Date.format(new Date(), 'yyyy-MM-dd HH:mm:ss');
        var currentRoles = new Roles(this.person.roles.filter(function(r){ return between(r, date, '5000'); }));
        this.$el.html(this.template(_.extend(this.model.toJSON(), {currentRoles: currentRoles})));
        this.$("[name=university]").css('max-width', '200px').css('width', '200px');
        this.$("[name=faculty]").css('max-width', '200px').css('width', '200px');
        this.$("[name=department]").css('max-width', '200px').css('width', '200px');
        this.$("[name=university]").combobox();
        this.$("[name=faculty]").combobox();
        this.$("[name=department]").combobox();
        if(positionsCombo && !(_.where(currentRoles.toJSON(), {role: HQP}).length > 0 && 
                               _.filter(currentRoles.toJSON(), function(r){ return !(r.role == HQP || r.role == PL || r.role == PS); }).length == 0)){
            this.$("[name=position]").css('max-width', '200px').css('width', '200px');
            this.$("[name=position]").combobox();
        }
        
        this.update();
        return this.$el;
    }, 
    
});

// View for editing the Level of Study
ManagePeopleEditLevelOfStudyView = Backbone.View.extend({

    parent: null,
    levels: [],

    initialize: function(options){
        this.parent = options.parent;
        this.model.fetch();
        this.template = _.template($('#edit_level_of_study_template').html());
        _.each(positionList, function(levels, position){
            this.levels = this.levels.concat(levels);
        }.bind(this));
        this.parent.universities.on("change", this.render);
        this.levels = _.uniq(this.levels);
        this.listenTo(this.model, "sync", function(){
            this.model.startTracking();
            this.render();
        }.bind(this));
    },

    events: {},
    
    activeLevels: function(){
        var activeLevels = [];
        _.each(this.parent.universities.models, function(university){
            if(university.get('deleted') == "true" || _.isUndefined(positionList[university.get('position')])){
                return;
            }
            activeLevels = activeLevels.concat(positionList[university.get('position')]);
        }.bind(this));
        return activeLevels;
    },
    
    render: function(){
        this.$el.html(this.template());
        this.$("select option").hide();
        var activeLevels = this.activeLevels();
        _.each(activeLevels, function(level){
            this.$("select option:contains(" + level + ")").show();
        }.bind(this));
        if(activeLevels.length == 0){
            this.parent.$("tfoot").hide();
            if(this.model.get('extra')['sub_position'] != "" && _.size(positionList) != 0){
                // Reset the sub_position value to empty string
                var extra = _.clone(this.model.get('extra'));
                extra['sub_position'] = "";
                this.model.set('extra', extra);
            }
        }
        else{
            this.parent.$("tfoot").show();
        }
        return this.$el;
    }

});
