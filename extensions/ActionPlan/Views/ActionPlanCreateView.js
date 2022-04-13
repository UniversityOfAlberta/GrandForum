ActionPlanCreateView = Backbone.View.extend({

    template: _.template($('#action_plan_create_template').html()),
    actions: undefined,
    dialog: undefined,

    initialize: function(options) {
        this.render();
        this.actions = options.actions;
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
                position: { 'my': 'center', 'at': 'center' },
                buttons: {
                    'Save': {
                        text: 'Create Action Plan',
                        click: function(){
                            this.model.save(null, {
                                success: function(){
                                    this.actions.fetch();
                                }.bind(this)
                            });
                            this.dialog.dialog('close');
                        }.bind(this)
                    },
                    'Cancel': function(){
                        this.dialog.dialog('close');
                    }.bind(this)
                }
            });
            $('.ui-dialog').addClass('program-body');
            $(window).resize(function(){
                if(this.dialog.is(":visible")){
                    this.dialog.dialog({
                        width: 'auto',
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
