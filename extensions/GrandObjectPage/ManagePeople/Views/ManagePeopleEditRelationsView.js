ManagePeopleEditRelationsView = Backbone.View.extend({

    relations: null,
    person: null,

    initialize: function(options){
        this.person = options.person;
        this.model.fetch();
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
        var copy = this.relations.where({'user2': this.person.get('id')})
        clearAllMessages();
        _.each(copy, $.proxy(function(relation){
            if(relation.get('deleted') != "true"){
                relation.save(null, {
                    success: function(){
                        addSuccess("Relations saved");
                    },
                    error: function(){
                        addError("Relations could not be saved");
                    }
                });
            }
            else {
                relation.destroy(null, {
                    success: function(){
                        addSuccess("Relations saved");
                    },
                    error: function(){
                        addError("Relations could not be saved");
                    }
                });
            }
        }, this));
    },
    
    addRelation: function(){
        this.relations.add(new PersonRelation({type: 'Works With', user1: me.get('id'), user2: this.person.get('id')}));
    },
    
    addRows: function(){
        var relations = new Backbone.Collection(this.relations.where({'user2': this.person.get('id')}));
        if(relations.length > 0){
            this.$("#relation_rows").empty();
        }
        relations.each($.proxy(function(relation, i){
            var view = new ManagePeopleEditRelationsRowView({model: relation});
            this.$("#relation_rows").append(view.render());
            if(i % 2 == 0){
                view.$el.addClass('even');
            }
            else{
                view.$el.addClass('odd');
            }
        }, this));
    },
    
    events: {
        "click #add": "addRelation"
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
        this.$("input[name=endDate]").val('0000-00-00');
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
        return this.$el;
    }, 
    
});
