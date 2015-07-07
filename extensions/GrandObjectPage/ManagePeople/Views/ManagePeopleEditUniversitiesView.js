ManagePeopleEditUniversitiesView = Backbone.View.extend({

    universities: null,
    person: null,

    initialize: function(options){
        this.person = options.person;
        this.model.fetch();
        this.template = _.template($('#edit_universities_template').html());
        
        this.model.ready().then($.proxy(function(){
            this.universities = this.model;
            this.listenTo(this.universities, "add", this.addRows);
            this.model.ready().then($.proxy(function(){
                this.render();
            }, this));
        }, this));
        
        var dims = {w:0, h:0};
        // Reposition the dialog when the window is resized or the dialog is resized
        setInterval($.proxy(function(){
	        if(this.$el.width() != dims.w || this.$el.height() != dims.h){
	            this.$el.dialog("option","position", {
                    my: "center bottom",
                    at: "center center"
                });
	            dims.w = this.$el.width();
	            dims.h = this.$el.height();
	        }
	    }, this), 100);
	    $(window).resize($.proxy(function(){
	        this.$el.dialog("option","position", {
                my: "center bottom",
                at: "center center"
            });
	    }, this));
    },
    
    saveAll: function(){
        var copy = this.universities.toArray();
        clearAllMessages();
        _.each(copy, $.proxy(function(university){
            if(university.get('deleted') != "true"){
                university.save(null, {
                    success: function(){
                        addSuccess("Universities saved");
                    },
                    error: function(){
                        addError("Universities could not be saved");
                    }
                });
            }
            else {
                university.destroy(null, {
                    success: function(){
                        addSuccess("Universities saved");
                    },
                    error: function(){
                        addError("Universities could not be saved");
                    }
                });
            }
        }, this));
    },
    
    addUniversity: function(){
        var university = "Unknown";
        this.universities.add(new PersonUniversity({university: university, department: 'Unknown', position: 'Unknown', personId: this.person.get('id')}));
    },
    
    addRows: function(){
        if(this.universities.length > 0){
            this.$("#university_rows").empty();
        }
        this.universities.each($.proxy(function(university){
            var view = new ManagePeopleEditUniversitiesRowView({model: university});
            this.$("#university_rows").append(view.render());
        }, this));
    },
    
    events: {
        "click #add": "addUniversity"
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
    
    initialize: function(){
        this.listenTo(this.model, "change", this.update);
        this.template = _.template($('#edit_universities_row_template').html());
    },
    
    delete: function(){
        this.model.delete = true;
    },
    
    // Sets the end date to infinite (0000-00-00)
    setInfinite: function(){
        this.$("input[name=endDate]").val('0000-00-00');
        this.model.set('endDate', '0000-00-00');
    },
    
    events: {
        "click #infinity": "setInfinite"
    },
    
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
        this.$("[name=university]").css('max-width', '200px');
        this.$("[name=department]").css('max-width', '200px');
        this.$("[name=position]").css('max-width', '200px');
        this.$("[name=university]").combobox();
        this.$("[name=department]").combobox();
        this.$("[name=position]").combobox();
        return this.$el;
    }, 
    
});
