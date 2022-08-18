ActionPlanView = Backbone.View.extend({

    template: _.template($('#action_plan_template').html()),
    dialog: undefined,

    initialize: function() {
        this.render();
    },

    events: {

    },

    render: function () {
        this.$el.html(this.template(this.model.toJSON()));
        if(this.dialog == undefined){
            this.dialog = this.$el.dialog({
                modal: true,
                draggable: false,
                resizable: false,
                width: 'auto',
                height: $(window).height()*0.85,
                position: { 'my': 'center', 'at': 'center' }
            });
            $('.ui-dialog').addClass('program-body');
            $(window).resize(function(){
                var desiredWidth = $(window).width()*0.75;
                if(window.matchMedia('(max-width: 767px)').matches){
                    desiredWidth = $(window).width()*0.99;
                }
                if(this.dialog.is(":visible")){
                    this.dialog.dialog({
                        width: desiredWidth,
                        height: $(window).height()*0.85,
                    });
                    this.dialog.dialog({
                        position: { 'my': 'center', 'at': 'center' }
                    });
                }
            }.bind(this)).resize();
        }
        return this.$el;
    }

});
