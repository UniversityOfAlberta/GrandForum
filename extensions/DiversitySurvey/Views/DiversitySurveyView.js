DiversitySurveyView = Backbone.View.extend({

    initialize: function(){
        this.model.once('sync', this.render, this);
        this.model.bind('change', this.change, this);
        this.model.bind('change:language', this.render, this);
        this.template_en = _.template($('#diversity_en_template').html());
        this.template_fr = _.template($('#diversity_fr_template').html());
        this.model.fetch({
            error: function(obj, e){
                clearAllMessages();
                addError(e.responseText);
            },
            success: function(){
                $(window).bind('keydown', function(event) {
                    if (event.ctrlKey || event.metaKey) {
                        switch (String.fromCharCode(event.which).toLowerCase()) {
                        case 's':
                            var focused = document.activeElement;
                            focused.blur();
                            event.preventDefault();
                            this.save();
                            break;
                        }
                    }
                }.bind(this));
            }.bind(this)
        });
    },
    
    events: {
        "click #save": "save"
    },
    
    save: _.debounce(function(){
        this.$("#save").prop("disabled", true);
        this.model.save(null, {
            success: function(){
                _.defer(function(){
                    clearAllMessages("#diversityMessages");
                    this.$("#diversityMessages").stop();
                    this.$("#diversityMessages").show();
                    this.$("#diversityMessages").css('opacity', 0.95);
                    addSuccess("Your Diversity Survey has been saved", false, "#diversityMessages");
                    this.$("#diversityMessages").fadeOut(10000);
                }.bind(this));
                this.$("#save").prop("disabled", false);
            }.bind(this),
            error: function(o, e){
                _.defer(function(){
                    clearAllMessages("#diversityMessages");
                    this.$("#diversityMessages").stop();
                    this.$("#diversityMessages").show();
                    this.$("#diversityMessages").css('opacity', 0.95);
                    if(e.responseText != undefined && e.responseText != ""){
                        addError(e.responseText, false, "#diversityMessages");
                    }
                    else{
                        addError("There was a problem saving your Diversity Survey", false, "#diversityMessages");
                    }
                    this.$("#diversityMessages").fadeOut(10000);
                });
                this.$("#save").prop("disabled", false);
            }.bind(this)
        });
    }, 200, true),
    
    change: function(initial){
        // Declining
        if(this.model.get('decline') == 1){
            if(initial === true){
                this.$("#reason").show();
                this.$("#survey").hide();
            }
            else{
                this.$("#reason").slideDown();
                this.$("#survey").slideUp();
            }
        }
        else{
            if(initial === true){
                this.$("#reason").hide();
                this.$("#survey").show();
            }
            else{
                this.$("#reason").slideUp();
                this.$("#survey").slideDown();
            }
        }
        
        // Birth Year
        if(this.model.get('birth') == "I prefer not to answer"){
            this.$("select[name=birth]").val("").prop("disabled", true);
        }
        else{
            this.$("select[name=birth]").prop("disabled", false);
        }
        
        // Indegenous
        if(this.model.get('indigenous') == "I prefer not to answer"){
            this.$("input[name=indigenous][type=radio]").prop("checked", false).prop("disabled", true);
        }
        else{
            this.$("input[name=indigenous][type=radio]").prop("disabled", false);
        }
        
        // Disability
        if(this.model.get('disability') == "I prefer not to answer"){
            this.$("input[name=disability][type=radio]").prop("checked", false).prop("disabled", true);
        }
        else{
            this.$("input[name=disability][type=radio]").prop("disabled", false);
        }
        
        // Disability Visibility
        if(this.model.get('disability') == "Yes"){
            if(initial === true){
                this.$("#disabilityVisibility").show();
            }
            else{
                this.$("#disabilityVisibility").slideDown();
            }
        }
        else{
            if(initial === true){
                this.$("#disabilityVisibility").hide();
            }
            else{
                this.$("#disabilityVisibility").slideUp();
            }
            this.$("input[name=disabilityVisibility]").prop("checked", false);
            this.model.set('disabilityVisibility', "");
        }
        if(this.model.get('disabilityVisibility') == "I prefer not to answer"){
            this.$("input[name=disabilityVisibility][type=radio]").prop("checked", false).prop("disabled", true);
        }
        else{
            this.$("input[name=disabilityVisibility][type=radio]").prop("disabled", false);
        }
        
        // Minority
        if(this.model.get('minority') == "I prefer not to answer"){
            this.$("input[name=minority][type=radio]").prop("checked", false).prop("disabled", true);
        }
        else{
            this.$("input[name=minority][type=radio]").prop("disabled", false);
        }
        
        // Race
        if(this.model.get('race').decline == "I prefer not to answer"){
            this.$("input[name=race_values][type=checkbox]").prop("checked", false).prop("disabled", true);
            this.$("input[name=race_other][type=text]").val("").prop("disabled", true);
            this.model.get('race').values = new Array();
            this.model.get('race').other = "";
        }
        else{
            this.$("input[name=race_values][type=checkbox]").prop("disabled", false);
            this.$("input[name=race_other][type=text]").prop("disabled", false);
        }
        
        // Racialized
        if(this.model.get('racialized') == "I prefer not to answer"){
            this.$("input[name=racialized][type=radio]").prop("checked", false).prop("disabled", true);
        }
        else{
            this.$("input[name=racialized][type=radio]").prop("disabled", false);
        }
        
        // Immigration
        if(this.model.get('immigration') == "I prefer not to answer"){
            this.$("input[name=immigration][type=radio]").prop("checked", false).prop("disabled", true);
            this.$("input[name=immigration][type=text]").val("").prop("disabled", true);
        }
        else{
            if(this.model.get('immigration') != "Canadian citizen" &&
               this.model.get('immigration') != "Permanent resident" &&
               this.model.get('immigration') != "Person from another country with a work or study permit"){
                this.$("input[name=immigration][type=radio]").prop("checked", false);
            }
            else{
                this.$("input[name=immigration][type=text]").val("");
            }
            this.$("input[name=immigration][type=radio]").prop("disabled", false);
            this.$("input[name=immigration][type=text]").prop("disabled", false);
        }
        
        // Gender
        if(this.model.get('gender').decline == "I prefer not to answer"){
            this.$("input[name=gender_values][type=checkbox]").prop("checked", false).prop("disabled", true);
            this.$("input[name=gender_other][type=text]").val("").prop("disabled", true);
            this.model.get('gender').values = new Array();
            this.model.get('gender').other = "";
        }
        else{
            this.$("input[name=gender_values][type=checkbox]").prop("disabled", false);
            this.$("input[name=gender_other][type=text]").prop("disabled", false);
        }
        
        // Orientation
        if(this.model.get('orientation').decline == "I prefer not to answer"){
            this.$("input[name=orientation_values][type=checkbox]").prop("checked", false).prop("disabled", true);
            this.$("input[name=orientation_other][type=text]").val("").prop("disabled", true);
            this.model.get('orientation').values = new Array();
            this.model.get('orientation').other = "";
        }
        else{
            this.$("input[name=orientation_values][type=checkbox]").prop("disabled", false);
            this.$("input[name=orientation_other][type=text]").prop("disabled", false);
        }
    },
    
    render: function(){
        if(this.model.get('language') == 'en' || this.model.get('language') == ''){
            main.set('title', networkName + ' Diversity Census Questionnaire');
            this.$el.html(this.template_en(this.model.toJSON()));
        }
        else if (this.model.get('language') == 'fr'){
            main.set('title', networkName + ' Questionnaire du Recensement sur la Diversité');
            this.$el.html(this.template_fr(this.model.toJSON()));
        }
        this.change(true);
        this.$el.on('change', 'input, select, textarea, button', function() {
            _.defer(function(){
                this.save();
            }.bind(this));
        }.bind(this));
        return this.$el;
    }

});
