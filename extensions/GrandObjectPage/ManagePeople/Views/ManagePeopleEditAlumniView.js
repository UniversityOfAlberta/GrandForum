ManagePeopleEditAlumniView = Backbone.View.extend({

    interval: null,

    initialize: function(options){
        this.model.fetch();
        this.template = _.template($('#edit_alumni_template').html());
        this.listenTo(this.model, "sync", this.render);
        this.listenTo(this.model, "change:recruited", function(){ this.updateFields(true)}.bind(this));
        this.listenTo(this.model, "change:alumni", function(){ this.updateFields(true)}.bind(this));

        // Reposition the dialog when the window is resized or the dialog is resized
        var dim = {w1: 0,
                   h1: 0,
                   w2: 0,
                   h2: 0};
        this.interval = setInterval(function(){
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
	    }.bind(this), 100);
    },
    
    events: {
    
    },
    
    updateFields: function(animate){
        // Recruited
        if(this.model.get('recruited') == "Recruited from outside Canada"){
            if(animate){
                this.$("#recruitedCountry").slideDown();
            }
            else{
                this.$("#recruitedCountry").show();
            }
        }
        else{
            if(animate){
                this.$("#recruitedCountry").slideUp();
            }
            else{
                this.$("#recruitedCountry").hide();
            }
        }
        
        // Alumni
        if(this.model.get('alumni') == "Yes, abroad"){
            if(animate){
                this.$("#alumniCountry").slideDown();
            }
            else{
                this.$("#alumniCountry").show();
            }
        }
        else{
            if(animate){
                this.$("#alumniCountry").slideUp();
            }
            else{
                this.$("#alumniCountry").hide();
            }
        }
    },
    
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        this.updateFields(false);
        return this.$el;
    }

});
