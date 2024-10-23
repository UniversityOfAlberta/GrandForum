ManagePeopleEditRelationsView = Backbone.View.extend({

    parent: null,
    relations: null,
    person: null,
    university: null,
    relationViews: null,

    initialize: function(options){
        this.parent = options.parent;
        this.person = options.person;
        this.university = options.university;
        this.relationViews = new Array();
        this.template = _.template($('#edit_relations_template').html());
        this.model.ready().then(function(){
            this.relations = this.model;
            this.listenTo(this.relations, "add", this.render);
            this.model.ready().then(function(){
                this.render();
            }.bind(this));
        }.bind(this));
        
        var dims = {w:0, h:0};
        // Reposition the dialog when the window is resized or the dialog is resized
        setInterval(function(){
	        if(this.$el.width() != dims.w || this.$el.height() != dims.h){
	            this.$el.dialog("option","position", {
                    my: "center center",
                    at: "center center",
                    offset: "0 -75%"
                });
	            dims.w = this.$el.width();
	            dims.h = this.$el.height();
	        }
	    }.bind(this), 100);
	    $(window).resize(function(){
	        this.$el.dialog("option","position", {
                my: "center center",
                at: "center center",
                offset: "0 -75%"
            });
	    }.bind(this));
    },
    
    clean: function(){
        _.each(this.relationViews, function(relView){
            relView.stopListening();
            relView.undelegateEvents();
        });
        this.stopListening();
        this.undelegateEvents();
    },
    
    disassociate: function(){
        var relations = new PersonRelations(this.relations.where({user2: this.person.get('id')}));
        var tmpRelations = new PersonRelations(this.relations.where({university: this.university.get('id')}));
        tmpRelations.each(function(relation){
            relation.set('personUniversity', null);
        }.bind(this));
    },
    
    getRelations: function(){
        var relations = new PersonRelations(this.relations.where({user2: this.person.get('id')}));
        if(this.university != null){
            var tmpRelations = new PersonRelations(this.relations.where({university: this.university.get('id')}));
            tmpRelations.each(function(relation){
                relation.set('personUniversity', this.university)
            }.bind(this));
        }
        relations = new Backbone.Collection(relations.filter(function(rel){
            return (rel.get('personUniversity') == this.university);
        }.bind(this)));
        return relations;
    },
    
    saveAll: function(){
        var person = this.person;
        var relations = this.getRelations();
        _.each(relations.models, function(relation){
            if(relation == null){
                // Probably removed from sortable
                return;
            }
            if(this.university != null){
                relation.set('university', this.university.get('id'));
            }
            if(relation.get('deleted') != "true"){
                if(!relation.save(null, {
                    success: function(){
                        
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
                    wait: true,
                    success: function(){

                    },
                    error: function(){
                        addError("Relation could not be saved");
                    }
                });
            }
        }.bind(this));
    },
    
    addRelation: function(){
        this.relations.add(new PersonRelation({type: 'Supervises', 
                                               user1: me.get('id'), 
                                               user2: this.person.get('id'),
                                               startDate: this.university.get('startDate'),
                                               endDate: this.university.get('endDate'),
                                               university: this.university.get('id'),
                                               personUniversity: this.university}));
    },
    
    addRows: function(){
        _.each(this.relationViews, function(relView){
            relView.stopListening();
            relView.undelegateEvents();
        });
        this.relationViews = new Array();
        var relations = this.getRelations();
        relations.each(function(relation, i){
            if(this.relationViews[i] == null){
                var view = new ManagePeopleEditRelationsRowView({model: relation, parent: this});
                this.$("#relation_rows").append(view.render());
                this.relationViews[i] = view;
            }
        }.bind(this));
    },
    
    sortable: function(){
        $(".sortableRelations").sortable({
            connectWith: ".sortableRelations",
            axis: "y",
            scroll: true,
            helper: function(e, tr){
                var $originals = tr.children();
                var $helper = tr.clone();
                tr.children().first().css('border-left', 'none');
                tr.children().last().css('border-right', 'none');
                return tr;
            },
            start: function(e, el){
                // Make sure that inputs have triggered their change event
                $(':focus').change();
                el.placeholder.height(el.item.height());
            },
            stop: function(e, el){
                var parentView = el.item.closest("td")[0].view;
                var thisView = el.item[0].view;
                var relation = thisView.model;
                var university = parentView.university;
                var universityId = el.item.parent().attr("data-id");
                relation.set('university', universityId);
                relation.set('personUniversity', university);
                parentView.relations.add(relation);
            }
        });
    },
    
    render: function(){
        this.$el.empty();
        this.$el.html(this.template());
        this.addRows();
        this.el.view = this;
        this.sortable();
        return this.$el;
    }

});

ManagePeopleEditRelationsRowView = Backbone.View.extend({
    
    parent: null,
    tagName: 'tr',
    
    initialize: function(options){
        this.parent = options.parent;
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
        var uniStart = (this.model.get('personUniversity') != null) ? this.model.get('personUniversity').get('startDate') : "";
        var uniEnd   = (this.model.get('personUniversity') != null) ? this.model.get('personUniversity').get('endDate').replace("0000-00-00", "9999-99-99") : "";
        var relStart = this.model.get('startDate');
        var relEnd   = this.model.get('endDate').replace("0000-00-00", "9999-99-99");
        
        uniStart = (uniStart != "") ? uniStart : "9999-99-99";
        uniEnd   = (uniEnd   != "") ? uniEnd   : "9999-99-99";
        relStart = (relStart != "") ? relStart : "9999-99-99";
        relEnd   = (relEnd   != "") ? relEnd   : "9999-99-99";
        
        if(this.model.get('deleted') == "true"){
            this.$el.addClass('deleted');
        }
        else{
            this.$el.removeClass('deleted');
        }
        this.$(".relError ul").empty();
        this.$(".endDateCell").removeClass("inlineWarning");
        this.$(".startDateCell").removeClass("inlineWarning");
        this.parent.parent.$("#uniEnd").removeClass("inlineWarning");
        if((this.model.get('status') == "Completed" ||
            this.model.get('status') == "Withdrew") &&
            (uniEnd == "" || uniEnd == "0000-00-00" || uniEnd == "9999-99-99")){
            this.parent.parent.$("#uniEnd").addClass("inlineWarning");
            this.$(".relError ul").append("<li>There should be a <b>University end date</b> when status is '" + this.model.get('status') + "'</li>").show();
        }
        if((this.model.get('status') == "Completed" ||
            this.model.get('status') == "Withdrew" ||
            this.model.get('status') == "Changed Supervisor") &&
           (this.model.get('endDate') == "" ||
            this.model.get('endDate') == "0000-00-00")){
            this.$(".endDateCell").addClass("inlineWarning");
            this.$(".relError ul").append("<li>There should be an end date when status is '" + this.model.get('status') + "'</li>").show();
        }
        if(this.model.get('personUniversity') != null && 
           ((uniStart > relStart && uniStart > relEnd) || 
            (uniEnd < relStart))){
            this.$(".startDateCell").addClass("inlineWarning");
            this.$(".endDateCell").addClass("inlineWarning");
            this.$(".relError ul").append("<li>The dates for this relation do not fall within the dates of the university</li>").show();
        }
    },
   
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        this.update();
        this.$("[name=status]").css('max-width', '228px').css('width', '228px');
        _.defer(function(){
            this.$("[name=startDate]").change();
            this.$("[name=endDate]").change();
        }.bind(this));
        this.el.view = this;
        return this.$el;
    }, 
    
});
