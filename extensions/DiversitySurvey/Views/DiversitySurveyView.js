DiversitySurveyView = Backbone.View.extend({

    xhr: null,

    initialize: function(){
        this.model.once('sync', this.render, this);
        this.model.bind('change:language', this.render, this);
        this.model.bind('change', this.change, this);
        this.template_en = _.template($('#diversity_en_template').html());
        this.template_fr = _.template($('#diversity_fr_template').html());
        this.model.fetch({
            error: function(obj, e){
                clearAllMessages();
                addError(e.responseText);
            }
        });
    },
    
    events: {
        "click #save": "save"
    },
    
    save: _.debounce(function(){
        this.$("#save").prop("disabled", true);
        this.xhr = this.model.save(null, {
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
                if(e.statusText == "abort"){
                    this.$("#save").prop("disabled", false);
                    return;
                }
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
    }, 1000),
    
    show: function(selector, initial){
        if(initial === true){
            this.$(selector).show();
        }
        else{
            this.$(selector).slideDown();
        }
    },
    
    hide: function(selector, initial){
        if(initial === true){
            this.$(selector).hide();
        }
        else{
            this.$(selector).slideUp();
        }
    },
    
    change: function(initial){
        // Declining
        if(this.model.get('decline') == 1){
            this.show("#reason", initial);
            this.hide("#survey", initial);
        }
        else{
            this.hide("#reason", initial);
            this.show("#survey", initial);
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
            this.show("#indigenousApply", initial);
        }
        else{
            this.hide("#indigenousApply", initial);
        }
        if(this.model.get('indigenousApply').decline == "Prefer not to say"){
            this.$("input[name=indigenousApply_values][type=checkbox]").prop("checked", false).prop("disabled", true);
            this.$("input[name=indigenousApply_other][type=text]").val("").prop("disabled", true);
            this.model.get('indigenousApply').values = new Array();
            this.model.get('indigenousApply').other = "";
        }
        else{
            if(this.model.get('indigenousApply').other != ""){
                this.$("input[name=indigenousApply_values][type=checkbox][value='Other']").prop("checked", true);
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
            this.show("#disabilityVisibility", initial);
        }
        else{
            this.hide("#disabilityVisibility", initial);
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
        
        // Race
        if(this.model.get('race').decline == "I prefer not to answer"){
            this.$("input[name=race_value][type=radio]").prop("checked", false).prop("disabled", true);
            this.model.get('race').value = "";
        }
        else{
            this.$("input[name=race_value][type=radio]").prop("disabled", false);
        }
        
        // Population Group
        if(this.model.get('race').decline2 == "I prefer not to answer"){
            this.$("input[name=race_values][type=checkbox]").prop("checked", false).prop("disabled", true);
            this.$("input[name=race_other][type=text]").val("").prop("disabled", true);
            this.model.get('race').values = new Array();
            this.model.get('race').other = "";
        }
        else{
            if(this.model.get('race').other != ""){
                this.$("input[name=race_values][type=checkbox][value='Other']").prop("checked", true);
            }
            this.$("input[name=race_values][type=checkbox]").prop("disabled", false);
            this.$("input[name=race_other][type=text]").prop("disabled", false);
        }
        
        // Affiliation
        if(this.model.get('affiliation') != "Network Investigator" && 
           this.model.get('affiliation') != "Highly Qualified Personnel" && 
           this.model.get('affiliation') != "Board and/or Committee Member" &&
           this.model.get('affiliation') != "Employee" &&
           this.model.get('affiliation') != "I prefer not to answer"){
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
        
        // Gender
        if(this.model.get('gender').decline == "I prefer not to answer"){
            this.$("input[name=gender_value][type=radio]").prop("checked", false).prop("disabled", true);
            this.$("input[name=gender_woman][type=radio]").prop("checked", false).prop("disabled", true);
            this.$("input[name=gender_man][type=radio]").prop("checked", false).prop("disabled", true);
            this.$("input[name=gender_other][type=text]").val("").prop("disabled", true);
            this.hide("#woman", initial);
            this.hide("#man", initial);
            this.model.get('gender').value = "";
            this.model.get('gender').woman = "";
            this.model.get('gender').man = "";
            this.model.get('gender').other = "";
        }
        else {
            this.$("input[name=gender_value][type=radio]").prop("disabled", false);
            this.$("input[name=gender_woman][type=radio]").prop("disabled", false);
            this.$("input[name=gender_man][type=radio]").prop("disabled", false);
            this.$("input[name=gender_other][type=text]").prop("disabled", false);
        }

        if(this.model.get('gender').value != "Other"){
            this.hide("#genderOther", initial);
        }
        else{
            this.show("#genderOther", initial);
        }
        if(this.model.get('gender').value != "Man"){
            this.$("input[name=gender_man][type=radio]").prop("checked", false);
            this.model.get('gender').man = "";
        }
        if(this.model.get('gender').value != "Woman"){
            this.$("input[name=gender_woman][type=radio]").prop("checked", false);
            this.model.get('gender').woman = "";
        }

        if(this.model.get('gender').value == "Woman"){
            this.show("#woman", initial);
            this.hide("#man", initial);
        }
        else if(this.model.get('gender').value == "Man"){
            this.hide("#woman", initial);
            this.show("#man", initial);
        }
        else{
            this.hide("#woman", initial);
            this.hide("#man", initial);
        }
        
        // Orientation
        if(this.model.get('orientation').decline == "I prefer not to answer"){
            this.$("input[name=orientation_value][type=radio]").prop("checked", false).prop("disabled", true);
            this.$("input[name=orientation_other][type=text]").val("").prop("disabled", true);
            this.model.get('orientation').value = "";
            this.model.get('orientation').other = "";
        }
        else{
            this.$("input[name=orientation_value][type=radio]").prop("disabled", false);
            this.$("input[name=orientation_other][type=text]").prop("disabled", false);
        }
        
        if(this.model.get('orientation').value != "Other"){
            this.hide("#orientationOther", initial);
        }
        else{
            this.show("#orientationOther", initial);
        }
        
        // Immigration
        if(this.model.get('immigration').decline == "I prefer not to answer"){
            this.$("input[name=immigration_value][type=radio]").prop("checked", false).prop("disabled", true);
            this.$("input[name=immigration_other][type=text]").val("").prop("disabled", true);
            this.model.get('immigration').value = "";
            this.model.get('immigration').other = "";
        }
        else{
            this.$("input[name=immigration_value][type=radio]").prop("disabled", false);
            this.$("input[name=immigration_other][type=text]").prop("disabled", false);
        }
        
        if(this.model.get('immigration').value != "Other"){
            this.hide("#immigrationOther", initial);
        }
        else{
            this.show("#immigrationOther", initial);
        }
        
        // Language Minority
        if(this.model.get('languageMinority').decline == "I prefer not to answer"){
            this.$("input[name=languageMinority_value][type=radio]").prop("checked", false).prop("disabled", true);
            this.$("input[name=languageMinority_yes][type=radio]").prop("checked", false).prop("disabled", true);
            this.hide("#languageMinorityYes", initial);
            this.model.get('languageMinority').value = "";
            this.model.get('languageMinority').yes = "";
        }
        else {
            this.$("input[name=languageMinority_value][type=radio]").prop("disabled", false);
            this.$("input[name=languageMinority_yes][type=radio]").prop("disabled", false);
        }

        if(this.model.get('languageMinority').value == "Yes"){
            this.show("#languageMinorityYes", initial);
        }
        else{
            this.$("input[name=languageMinority_yes][type=radio]").prop("checked", false);
            this.model.get('languageMinority').yes = "";
            this.hide("#languageMinorityYes", initial);
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
        this.$el.on('change', 'input, select, textarea, button', function(){
            if(this.xhr != null){
                this.xhr.abort();
            }
            _.defer(function(){
                this.save();
            }.bind(this));
        }.bind(this));
        return this.$el;
    }

});
