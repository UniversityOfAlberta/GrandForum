DiversitySurveyView = Backbone.View.extend({

    initialize: function(){
        this.model.bind('sync', this.render, this);
        this.model.bind('change', this.change, this);
        this.template = _.template($('#diversity_template').html());
        _.defer(this.render);
    },
    
    events: {
    
    },
    
    change: function(initial){
        // Declining
        console.log(this.model.toJSON());
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
    },
    
    render: function(){
        this.$el.html(this.template(this.model.toJSON()));
        this.change(true);
        return this.$el;
    }

});
