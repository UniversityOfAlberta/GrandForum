ManagePeopleEditSubRolesView = Backbone.View.extend({

    interval: null,

    initialize: function(options){
        this.model.fetch();
        this.template = _.template($('#edit_subroles_template').html());
        
        this.listenTo(this.model, "sync", this.render);

        // Reposition the dialog when the window is resized or the dialog is resized
        var dim = {w1: 0,
                   h1: 0,
                   w2: 0,
                   h2: 0};
        this.interval = setInterval($.proxy(function(){
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
	    }, this), 100);
    },
    
    events: {
    
    },
    
    render: function(){
        this.$el.html(this.template());
        return this.$el;
    }

});
