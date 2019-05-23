CoursesEditView = Backbone.View.extend({
    isDialog: false,
    sops: null,
    parent: null,
    initialize: function(){
	this.parent = this;
        this.template = _.template($('#courses_edit_template').html());
        this.listenTo(this.model, "sync", function(){
            this.sops = this.model;
            this.render();
        }, this);
    },
    
    events: {
        "click #saveCourse": "saveCourse",
        "click #cancel": "cancel",
    },

    validate: function(){
        if(this.model.get('subject').trim() == ""){
            return "The course must have a subject";
        }
        else if(this.model.get('catalog').trim() == ""){
            return "The course must have a catalog";
        }
        else if(this.model.get('component').trim() == ""){
            return "The course must have a component";
        }
        return "";
    },

    saveCourse: function(){
        var validation = this.validate();
        if(validation != ""){
            clearAllMessages();
            addError(validation, true);
            return;
        }
        this.$(".throbber").show();
        this.$("#saveCourse").prop('disabled', true);
        this.model.save(null, {
            success: function(){
                this.$(".throbber").hide();
                this.$("#saveCourse").prop('disabled', false);
                clearAllMessages();
                document.location = this.model.get('course_url');
            }.bind(this),
            error: function(o, e){
                this.$(".throbber").hide();
                this.$("#saveCourse").prop('disabled', false);
                clearAllMessages();
                if(e.responseText != ""){
                    addError(e.responseText, true);
                }
                else{
                    addError("There was a problem saving the Course", true);
                }
            }.bind(this)
        });
    },

    cancel: function(){
        document.location = this.model.get('course_url');
    },


    render: function(){
    if(this.model.get('subject') == ""){
	main.set('title', "Course");
    }
    else{
        main.set('title',this.model.get('subject') + ' ' + this.model.get('catalog') + ' (' + this.model.get('component') + ')');
    }
      	var self = this;
        var mod = _.extend(this.model.toJSON());
        this.el.innerHTML = this.template(mod);
        return this.$el;
    }
});
