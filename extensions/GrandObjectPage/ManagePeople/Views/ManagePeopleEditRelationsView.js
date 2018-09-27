ManagePeopleEditRelationsView = Backbone.View.extend({

    relations: null,
    person: null,
    relationViews: null,

    initialize: function(options){
        this.person = options.person;
        this.model.fetch();
        this.relationViews = new Array();
        this.template = _.template($('#edit_relations_template').html());
        this.model.ready().then($.proxy(function(){
            this.relations = this.model;
            this.listenTo(this.relations, "add", this.addRows);
            this.model.ready().then($.proxy(function(){
                this.render();
            }, this));
        }, this));
        
        var dims = {w:0, h:0};
        // Reposition the dialog when the window is resized or the dialog is resized
        setInterval($.proxy(function(){
	        if(this.$el.width() != dims.w || this.$el.height() != dims.h){
	            this.$el.dialog("option","position", {
                    my: "center center",
                    at: "center center",
                    offset: "0 -75%"
                });
	            dims.w = this.$el.width();
	            dims.h = this.$el.height();
	        }
	    }, this), 100);
	    $(window).resize($.proxy(function(){
	        this.$el.dialog("option","position", {
                my: "center center",
                at: "center center",
                offset: "0 -75%"
            });
	    }, this));
    },
    
    saveAll: function(){
        var person = this.person;
        var copy = this.relations.where({'user2': person.get('id')})
        clearAllMessages();
        _.each(copy, $.proxy(function(relation){
            if(relation.get('deleted') != "true"){
                if(!relation.save(null, {
                    success: function(){
                        addSuccess("Relation saved");
                        person.fetch();
                    },
                    error: function(){
                        addError("Relation could not be saved");
                    }
                })){
                    addError(relation.validationError);
                };
            }
            else {
                relation.destroy({
                    success: function(){
                        addSuccess("Relation saved");
                        person.fetch();
                    },
                    error: function(){
                        addError("Relation could not be saved");
                    }
                });
            }
        }, this));
    },
    
    addRelation: function(){
        this.relations.add(new PersonRelation({type: 'Supervises', user1: me.get('id'), user2: this.person.get('id')}));
        this.$el.scrollTop(this.el.scrollHeight);
    },
    
    addRows: function(){
        var relations = new Backbone.Collection(this.relations.where({'user2': this.person.get('id')}));
        relations.each($.proxy(function(relation, i){
            if(this.relationViews[i] == null){
                var view = new ManagePeopleEditRelationsRowView({model: relation});
                this.$("#relation_rows").append(view.render());
                if(i % 2 == 0){
                    view.$el.addClass('even');
                }
                else{
                    view.$el.addClass('odd');
                }
                this.relationViews[i] = view;
            }
        }, this));
    },
    
    render: function(){
        this.$el.empty();
        this.$el.html(this.template());
        this.addRows();
        return this.$el;
    }

});

ManagePeopleEditRelationsRowView = Backbone.View.extend({
    
    tagName: 'tr',
    
    initialize: function(){
        this.listenTo(this.model, "change", this.update);
        this.template = _.template($('#edit_relations_row_template').html());
    },
    
    delete: function(){
        this.model.delete = true;
    },
    
    // Sets the end date to infinite (0000-00-00)
    setInfinite: function(){
        this.$("input[name=endDate]").val('').change();
        this.model.set('endDate', '');
        this.changeEnd();
    },
    
    events: {
        "click #infinity": "setInfinite",
        "change [name=startDate]": "changeStart",
        "change [name=endDate]": "changeEnd"
    },
    
    changeStart: function(){
        // These probably won't exist in most cases, but if they do, then yay
        var start_date = this.$("[name=startDate]").val();
        var end_date = this.$("[name=endDate]").val();
        if(start_date != "" && start_date != "0000-00-00"){
            this.$("[name=endDate]").datepicker("option", "minDate", start_date);
        }
    },
    
    changeEnd: function(){
        // These probably won't exist in most cases, but if they do, then yay
        var start_date = this.$("[name=startDate]").val();
        var end_date = this.$("[name=endDate]").val()
        if(end_date != "" && end_date != "0000-00-00"){
            this.$("[name=startDate]").datepicker("option", "maxDate", end_date);
        }
        else{
            this.$("[name=startDate]").datepicker("option", "maxDate", null);
        }
    },
    
    update: function(){
        if(this.model.get('deleted') == "true"){
            this.$el.addClass('deleted');
        }
        else{
            this.$el.removeClass('deleted');
        }
        if((this.model.get('status') == "Completed" ||
            this.model.get('status') == "Withdrew" ||
            this.model.get('status') == "Changed Supervisor") &&
           (this.model.get('endDate') == "" ||
            this.model.get('endDate') == "0000-00-00")){
            this.$(".endDateCell").css("background", "#FF8800");
            this.$(".relError").text("There should be an end date when status is '" + this.model.get('status') + "'").show();
        }
        else if((this.model.get('status') == "Continuing") &&
           (this.model.get('endDate') != "" &
            this.model.get('endDate') != "0000-00-00")){
            this.$(".endDateCell").css("background", "#FF8800");
            this.$(".relError").text("There should be no end date when status is '" + this.model.get('status') + "'").show();
        }
        else{
            this.$(".endDateCell").css("background", "");
            this.$(".relError").text("").hide();
        }
    },
   
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        this.update();
        _.defer($.proxy(function(){
            this.$("[name=startDate]").change();
            this.$("[name=endDate]").change();
        }, this));
        return this.$el;
    }, 
    
});
