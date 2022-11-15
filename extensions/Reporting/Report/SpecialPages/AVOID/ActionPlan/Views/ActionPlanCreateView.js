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

    changeType: function(){
        this.$("[name=components_A]," +
               "[name=components_V]," +
               "[name=components_O]," + 
               "[name=components_I]," + 
               "[name=components_D]," + 
               "[name=components_S]," + 
               "[name=components_F]").prop("checked", false).change();
        _.defer(function(){
            if(this.model.get('type') == ActionPlan.MANUAL){
                this.$("#manual").show();
                this.$("#fitbit").hide();
            }
            else if(this.model.get('type') == ActionPlan.FITBIT){
                this.$("#manual").hide();
                this.$("#fitbit").show();
                this.changeFitbit();
                if($.cookie('fitbit') == undefined){
                    this.authorizeFitBit();
                }
            }
        }.bind(this));
    },

    authorizeFitBit: function(){
        var url = "https://www.fitbit.com/oauth2/authorize?response_type=token" +
                  "&client_id=" + fitbitId +
                  "&redirect_uri=" + document.location.origin + document.location.pathname + "?fitbitApi" +
                  "&scope=activity%20nutrition%20sleep%20heartrate&expires_in=31536000";
        var popup = window.open(url,'popUpWindow','height=600,width=500,left=100,top=100,resizable=yes,scrollbars=yes,toolbar=yes,menubar=no,location=no,directories=no, status=yes');
        var popupInterval = setInterval(function(){
            if(popup == null || popup.closed){
                clearInterval(popupInterval);
                clearError('#fitbitMessages');
                if($.cookie('fitbit') == undefined){
                    // Failed
                    this.model.set('type', ActionPlan.MANUAL);
                    this.render();
                    addError('There was an error connecting to your Fitbit account.  Make sure that you checked "Allow All" when authorizing AVOID to access your Fitbit data.', false, '#fitbitMessages');
                }
            }
        }.bind(this), 500);
    },
    
    changeFitbit: function(){
        _.defer(function(){
            if(this.model.get('fitbit').steps > 0 || this.model.get('fitbit').distance > 0 || this.model.get('fitbit').activity > 0){
                this.$("[name=components_A]").prop("checked", true).change();
            }
            else{
                this.$("[name=components_A]").prop("checked", false).change();
            }
            
            if(this.model.get('fitbit').sleep > 0){
                this.$("[name=components_S]").prop("checked", true).change();
            }
            else{
                this.$("[name=components_S]").prop("checked", false).change();
            }
            
            if(this.model.get('fitbit').water > 0 || this.model.get('fitbit').protein > 0 || this.model.get('fitbit').fibre > 0){
                this.$("[name=components_D]").prop("checked", true).change();
            }
            else{
                this.$("[name=components_D]").prop("checked", false).change();
            }
        }.bind(this));
    },
    
    events: {
        "change [name=type]": "changeType",
        "change .fitbitFields input": "changeFitbit",
    },

    render: function (){
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
                                    clearSuccess('#actionPlanMessages');
                                    addSuccess('Action Plan created!', false, '#actionPlanMessages');
                                    this.actions.fetch();
                                }.bind(this),
                                error: function(){
                                    clearError('#actionPlanMessages');
                                    addError('Error creating action plan', false, '#actionPlanMessages');
                                }
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
            this.$("[name=fitbit_steps]").forceNumeric({min: 0, max: 100000, decimals: 0});
            this.$("[name=fitbit_distance]").forceNumeric({min: 0, max: 1000, decimals: 2});
            this.$("[name=fitbit_sleep]").forceNumeric({min: 0, max: 24, decimals: 0});
            this.$("[name=fitbit_water]").forceNumeric({min: 0, max: 10000, decimals: 0});
            this.$("[name=fitbit_protein]").forceNumeric({min: 0, max: 10000, decimals: 0});
        }
        this.changeType();
        this.validations();
        return this.$el;
    }

});
