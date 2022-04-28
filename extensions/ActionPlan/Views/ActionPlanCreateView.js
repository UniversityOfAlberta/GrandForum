ActionPlanCreateView = Backbone.View.extend({

    template: _.template($('#action_plan_create_template').html()),
    actions: undefined,
    dialog: undefined,

    initialize: function(options) {
        this.render();
        this.actions = options.actions;
        this.model.bind("change", this.validations, this);
    },
    
    validations: function(){
        if(this.model.getComponents().length > 0){
            $("#saveActionPlanButton").button("option", "disabled", false);
            $(".ui-dialog-buttonset .warning", this.$el.parent()).hide();
        }
        else{
            $("#saveActionPlanButton").button("option", "disabled", true);
            $(".ui-dialog-buttonset .warning", this.$el.parent()).show();
            $(".ui-dialog-buttonset .warning", this.$el.parent()).text("You must select at least one AVOID component");
        }
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
                        id: "saveActionPlanButton",
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
            $(".ui-dialog-buttonset", this.$el.parent()).prepend("<div class='warning'></div>");
            $(".ui-dialog-buttonset .warning", this.$el.parent()).css('display', 'inline-block')
                                                                 .css('margin', 0)
                                                                 .css('margin-top', '0')
                                                                 .css('margin-bottom', '5px')
                                                                 .css('font-size', '1em')
                                                                 .css('float', 'left')
                                                                 .css('padding-right', '15px')
        }
        this.validations();
        return this.$el;
    }

});
