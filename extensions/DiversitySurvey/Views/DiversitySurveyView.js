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
                    if(this.model.get('affiliation') == "" && this.model.get('decline') != 1){
                        addWarning("Your Diversity Survey has been saved, but Question 1 has not been answered", false, "#diversityMessages");
                    }
                    else{
                        addSuccess("Your Diversity Survey has been saved", false, "#diversityMessages");
                    }
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
    }, 1000, false),
    
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
        
        // Indigenous
        if(this.model.get('indigenous') == "I prefer not to answer"){
            this.$("input[name=indigenous][type=radio]").prop("checked", false).prop("disabled", true);
        }
        else{
            this.$("input[name=indigenous][type=radio]").prop("disabled", false);
        }
        
        // Indigenous Visibility
        if(this.model.get('indigenous') == "Yes"){
            if(initial === true){
                this.$("#indigenousApply").show();
            }
            else{
                this.$("#indigenousApply").slideDown();
            }
        }
        else{
            if(initial === true){
                this.$("#indigenousApply").hide();
            }
            else{
                this.$("#indigenousApply").slideUp();
            }
        }
        if(this.model.get('indigenousApply').decline == "I prefer not to answer"){
            this.$("input[name=indigenousApply_values][type=checkbox]").prop("checked", false).prop("disabled", true);
            this.$("input[name=indigenousApply_other][type=text]").val("").prop("disabled", true);
            this.model.get('indigenousApply').values = new Array();
            this.model.get('indigenousApply').other = "";
        }
        else{
            if(this.model.get('indigenousApply').other != ""){
                this.$("input[name=indigenousApply_values][type=checkbox][value='Another']").prop("checked", true);
            }
            this.$("input[name=indigenousApply_values][type=checkbox]").prop("disabled", false);
            this.$("input[name=indigenousApply_other][type=text]").prop("disabled", false);
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
        }
        if(this.model.get('disabilityVisibility').decline == "I prefer not to answer"){
            this.$("input[name=disabilityVisibility_values][type=checkbox]").prop("checked", false).prop("disabled", true);
            this.$("input[name=disabilityVisibility_other][type=text]").val("").prop("disabled", true);
            this.model.get('disabilityVisibility').values = new Array();
            this.model.get('disabilityVisibility').other = "";
        }
        else{
            if(this.model.get('disabilityVisibility').other != ""){
                this.$("input[name=disabilityVisibility_values][type=checkbox][value='Another']").prop("checked", true);
            }
            this.$("input[name=disabilityVisibility_values][type=checkbox]").prop("disabled", false);
            this.$("input[name=disabilityVisibility_other][type=text]").prop("disabled", false);
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
        if(this.model.get('race').values.indexOf("Visible Minority") != -1){
            if(initial === true){
                this.$("#minorities").show();
            }
            else{
                this.$("#minorities").slideDown();
            }
        }
        else{
            this.$("#minorities input[name=race_values][type=checkbox]").prop("checked", false);
            this.$("#minorities input[name=race_other][type=text]").val("");
            this.model.get('race').values = _.difference(this.model.get('race').values, 
                                                         _.map($('#minorities input'), function(el){ return $(el).val(); }));
            this.model.get('race').other = "";
            if(initial === true){
                this.$("#minorities").hide();
            }
            else{
                this.$("#minorities").slideUp();
            }
        }
        
        // Racialized
        if(this.model.get('racialized') == "I prefer not to answer"){
            this.$("input[name=racialized][type=radio]").prop("checked", false).prop("disabled", true);
        }
        else{
            this.$("input[name=racialized][type=radio]").prop("disabled", false);
        }
        
        // Affiliation
        if(this.model.get('affiliation') != "Network Investigator" && 
           this.model.get('affiliation') != "Highly Qualified Personnel" && 
           this.model.get('affiliation') != "Board and/or Committee Member" &&
           this.model.get('affiliation') != "Employee"){
            this.$("input[name=affiliation][type=radio]").prop("checked", false);
            if(this.model.get('affiliation') != ''){
                this.$("input[name=affiliation][type=radio][value=Other]").prop("checked", true);
                if(this.model.get('affiliation') == 'Other'){
                    this.$("input[name=affiliation][type=text]").val("");
                }
            }
        }
        else{
            this.$("input[name=affiliation][type=text]").val("");
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
            if(this.model.get('gender').other != ""){
                this.$("input[name=gender_values][type=checkbox][value='Another gender']").prop("checked", true);
            }
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
            if(this.model.get('orientation').other != ""){
                this.$("input[name=orientation_values][type=checkbox][value='Another orientation']").prop("checked", true);
            }
            this.$("input[name=orientation_values][type=checkbox]").prop("disabled", false);
            this.$("input[name=orientation_other][type=text]").prop("disabled", false);
        }
        
        // Improve
        if(this.model.get('improve').other != ""){
            this.$("input[name=improve_values][type=checkbox][value='Other']").prop("checked", true);
        }
        
        // Respected
        if(this.model.get('respected').decline == "I prefer not to answer"){
            this.$("input[name=respected_values][type=checkbox]").prop("checked", false).prop("disabled", true);
            this.model.get('respected').values = new Array();
            this.model.get('respected').other = "";
        }
        else{
            this.$("input[name=respected_values][type=checkbox]").prop("disabled", false);
        }
        
        // Least Respected
        if(this.model.get('leastRespected').decline == "I prefer not to answer"){
            this.$("input[name=leastRespected_values][type=checkbox]").prop("checked", false).prop("disabled", true);
            this.model.get('leastRespected').values = new Array();
        }
        else{
            this.$("input[name=leastRespected_values][type=checkbox]").prop("disabled", false);
        }
        
        // Prevents Training
        if(this.model.get('training') == "I’ve never taken any EDI training"){
            if(initial === true){
                this.$("#preventsTraining").show();
            }
            else{
                this.$("#preventsTraining").slideDown();
            }
        }
        else{
            if(initial === true){
                this.$("#preventsTraining").hide();
            }
            else{
                this.$("#preventsTraining").slideUp();
            }
        }
        if(this.model.get('preventsTraining').other != ""){
            this.$("input[name=preventsTraining_values][type=checkbox][value='Other']").prop("checked", true);
        }
        
        // Training Taken
        if(this.model.get('trainingTaken').other != ""){
            this.$("input[name=trainingTaken_values][type=checkbox][value='Other']").prop("checked", true);
        }
    },
    
    render: function(){
        if(this.model.get('language') == 'en' || this.model.get('language') == ''){
            main.set('title', networkName + ' Diversity Census Questionnaire');
            this.$el.html(this.template_en(this.model.toJSON()));
        }
        else if (this.model.get('language') == 'fr'){
            main.set('title', 'Questionnaire sur la Diversité et l’Inclusion ' + networkName);
            this.$el.html(this.template_fr(this.model.toJSON()));
        }
        this.change(true);
        this.$el.on('change', 'input, select, textarea, button', function() {
            this.save();
        }.bind(this));
        return this.$el;
    }

});
