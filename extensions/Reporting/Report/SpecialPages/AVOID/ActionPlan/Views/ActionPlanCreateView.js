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
            $("#saveActionPlanButton").prop("disabled", false);
            $(".actionPlanWarning", this.$el.parent()).slideUp();
        }
        else{
            $("#saveActionPlanButton").prop("disabled", true);
            $(".actionPlanWarning", this.$el.parent()).slideDown();
            $(".actionPlanWarning", this.$el.parent()).html("<en>You must select at least one AVOID component</en><fr>Sélectionnez au moins un domaine Proactif.</fr>");
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
        this.$("[name=dates_Mon]," +
               "[name=dates_Tue]," +
               "[name=dates_Wed]," + 
               "[name=dates_Thu]," + 
               "[name=dates_Fri]," + 
               "[name=dates_Sat]," + 
               "[name=dates_Sun]").prop("checked", false).change();
        _.defer(function(){
            if(this.model.get('type') == ActionPlan.MANUAL){
                this.$(".manual").show().parent().removeClass("skip");
                this.$(".fitbit").hide().parent().addClass("skip");
            }
            else if(this.model.get('type') == ActionPlan.FITBIT){
                this.$("[name=dates_Mon]," +
                       "[name=dates_Tue]," +
                       "[name=dates_Wed]," + 
                       "[name=dates_Thu]," + 
                       "[name=dates_Fri]," + 
                       "[name=dates_Sat]," + 
                       "[name=dates_Sun]").prop("checked", true).change();
                this.$(".manual").hide().parent().addClass("skip");
                this.$(".fitbit").show().parent().removeClass("skip");
                this.changeFitbit();
            }
        }.bind(this));
    },
    
    changeConfidence: function(){
        _.defer(function(){
            if(this.model.get('confidence') < 7){
                this.$(".confidenceWarning").slideDown();
            }
            else{
                this.$(".confidenceWarning").slideUp();
            }
        }.bind(this));
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
        "change [name=confidence]": "changeConfidence",
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
                title: '<en>My Weekly Action Plan</en><fr>Mon plan d’action hebdomadaire</fr>',
                height: $(window).height()*0.85,
                position: { 'my': 'center', 'at': 'center' },
                buttons: {
                    'Previous': {
                        id: "previousActionPlanSection",
                        text: (wgLang == 'en') ? "Previous" : "Précédent",
                        click: function(){
                            if(this.$(".actionPlanSection.open").prevAll(":not(.skip)").first().length > 0){
                                this.$(".actionPlanSection.open").slideUp().removeClass("open").prevAll(":not(.skip)").first()
                                                                 .slideDown().addClass("open");
                                $("#nextActionPlanSection").show();
                                $("#saveActionPlanButton").hide();
                                if(this.$(".actionPlanSection.open").prevAll(":not(.skip)").first().length == 0){
                                    $("#previousActionPlanSection").prop("disabled", true);
                                }
                            }
                        }.bind(this)
                    },
                    'Next': {
                        id: "nextActionPlanSection",
                        text: (wgLang == 'en') ? "Next" : "Prochain",
                        click: function(){
                            $("#previousActionPlanSection").prop("disabled", false);
                            if(this.$(".actionPlanSection.open").nextAll(":not(.skip)").first().length > 0){
                                this.$(".actionPlanSection.open").slideUp().removeClass("open").nextAll(":not(.skip)").first()
                                                                 .slideDown().addClass("open");
                                if(this.$(".actionPlanSection.open").nextAll(":not(.skip)").first().length == 0){
                                    $("#nextActionPlanSection").hide();
                                    $("#saveActionPlanButton").show();
                                }
                            }
                        }.bind(this)
                    },
                    'Save': {
                        id: "saveActionPlanButton",
                        text: (wgLang == 'en') ? "Create Plan" : "Créer un plan",
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
                    }
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
            
            $("#previousActionPlanSection, #nextActionPlanSection, #saveActionPlanButton").attr("class", "")
                                                                                          .addClass("program-button")
                                                                                          .css("font-size", "1em")
                                                                                          .css("line-height", "1.5em")
                                                                                          .css("width", "8em");
            
            $("#previousActionPlanSection").css('float', 'left').prop("disabled", true);
            $("#saveActionPlanButton").hide();
            this.$("[name=fitbit_steps]").forceNumeric({min: 0, max: 100000, decimals: 0});
            this.$("[name=fitbit_distance]").forceNumeric({min: 0, max: 1000, decimals: 2});
            this.$("[name=fitbit_sleep]").forceNumeric({min: 0, max: 24, decimals: 0});
            this.$("[name=fitbit_water]").forceNumeric({min: 0, max: 10000, decimals: 0});
            this.$("[name=fitbit_protein]").forceNumeric({min: 0, max: 10000, decimals: 0});
            this.$(".actionPlanSection").hide().removeClass("open").first()
                                        .show().addClass("open");
        }
        this.changeType();
        this.changeConfidence();
        this.validations();
        return this.$el;
    }

});
