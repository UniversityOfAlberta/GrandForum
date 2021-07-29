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
        university.set("position", "Unknown");
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
    },
   
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        this.$("[name=university]").css('max-width', '200px').css('width', '200px');
        this.$("[name=department]").css('max-width', '200px').css('width', '200px');
        this.$("[name=university]").combobox();
        this.$("[name=department]").combobox();
        if(!(_.where(this.person.roles.toJSON(), {role: HQP}).length > 0 && 
             _.filter(this.person.roles.toJSON(), function(r){ return !(r.role == HQP || r.role == PL || r.role == PS); }).length == 0)){
            this.$("[name=position]").css('max-width', '200px').css('width', '200px');
            this.$("[name=position]").combobox();
        }
        
        this.update();
        return this.$el;
    }, 
    
});
