CourseView = Backbone.View.extend({

    sops: null,

    initialize: function(){
        this.template = _.template($('#course_template').html());
        this.listenTo(this.model, "sync", function(){
            this.sops = this.model;
            this.render();
        }, this);
    },
    
    events: {
        "click #editCourse": "editCourse",
    },

    editCourse: function(){
        document.location = document.location + '/edit';
    },

    render: function(){
    	main.set('title', this.model.get('subject') + ' ' + this.model.get('catalog') + ' (' + this.model.get('component') + ')');
      	var self = this;
	console.log(self.model);
        var mod = _.extend(this.model.toJSON());
        this.el.innerHTML = this.template(mod);
        return this.$el;
    }
});
