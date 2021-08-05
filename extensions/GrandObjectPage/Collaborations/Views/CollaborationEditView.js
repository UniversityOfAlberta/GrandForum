CollaborationEditView = Backbone.View.extend({

    isDialog: false,

    initialize: function(){
        this.parent = this;
        this.listenTo(this.model, "sync", this.render);
        this.listenTo(this.model, "change:title", function(){
            if(!this.isDialog){
                main.set('title', this.model.get('title'));
            }
        });
        
        this.template = _.template($('#collaboration_edit_template').html());

        if(!this.model.isNew() && !this.isDialog){
            this.model.fetch();
        }
        else{
            _.defer(this.render);
        }
    },
    
    saveCollaboration: function(){
        if (this.model.get("title").trim() == '') {
            clearWarning();
            addWarning('Organization name must not be empty', true);
            return;
        }
        if(!this.updateContactWarning()){
            clearWarning();
            addWarning("This "+  this.model.getType().toLowerCase() + " does not have a contact name and position specified", true);
            return;
        }
        if(this.model.get("number") == "" || !(parseInt(this.model.get("number")) >= 0 && parseInt(this.model.get("number")) <= 999)){
            clearWarning();
            addWarning("This "+  this.model.getType().toLowerCase() + " must have an estimated number of collaborators specified between 0 and 999", true);
            return;
        }
        if(!this.updateCountryWarning()){
            clearWarning();
            addWarning("This "+  this.model.getType().toLowerCase() + " does not have a country and sector specified", true);
            return;
        }
        if(!this.updateDescriptionWarning()){
            clearWarning();
            addWarning("This " + this.model.getType().toLowerCase() + " does not have a description specified", true);
            return;
        }
        if(!this.updateExistedWarning()){
            clearWarning();
            addWarning("This " + this.model.getType().toLowerCase() + " is incomplete.", true);
            return;
        }
        if(!this.updateFundsWarning()){
            clearWarning();
            addWarning("This " + this.model.getType().toLowerCase() + " does not have funding information, or is not in the form of a number.", true);
            return;
        }
        if(!this.updateFiles()){
            clearWarning();
            addWarning("The max file size is 2MB per file", true);
            return;
        }
        if(!this.updateThemesWarning()){
            clearWarning();
            addWarning("This " + this.model.getType().toLowerCase() + " does not have any attributes selected.", true);
            return;
        }
        if(!this.updateProjects()){
            clearWarning();
            addWarning("This " + this.model.getType().toLowerCase() + " does not have any associated projects.", true);
            return;
        }
        this.$(".throbber").show();
        this.$("#saveCollaboration").prop('disabled', true);
        this.model.save(null, {
            success: function(){
                this.$(".throbber").hide();
                this.$("#saveCollaboration").prop('disabled', false);
                clearAllMessages();
                document.location = this.model.get('url');
            }.bind(this),
            error: function(o, e){
                this.$(".throbber").hide();
                this.$("#saveCollaboration").prop('disabled', false);
                clearAllMessages();
                if(e.responseText != ""){
                    addError(e.responseText, true);
                }
                else{
                    addError("There was a problem saving the Collaboration", true);
                }
            }.bind(this)
        });
    },
    
    cancel: function(){
        if(this.model.get('id') > 0){
            document.location = this.model.get('url');
        }
        else{
            // Doesn't exist yet
            document.location = "#/";
        }
    },
    
    events: {
        "click #saveCollaboration": "saveCollaboration",
        "click #cancel": "cancel",
        "click .collab_check": "checkCollabItem",
        "change input[name=fund]": "toggleFunding",
    },

    checkCollabItem: function(data) {
        if ($(data.target).prop("tagName") != "INPUT") {
            var checkbox = $('input[type=checkbox]', data.currentTarget);
            var checked = checkbox.is(':checked');
            checkbox.prop('checked', !checked).change();
        }
    },
    
    toggleFunding: function(data) {
        var funded = this.$('input:radio[name=fund]:checked').val();
        var fundAmtDiv = this.$('#fundingAmount');
        this.model.attributes['cash'] = $('input[name=cash]').val();
        this.model.attributes['inkind'] = $('input[name=inkind]').val();
        if (funded == "yes") {
            fundAmtDiv.slideDown();
        } else {
            this.model.attributes['cash'] = 0;
            this.model.attributes['inkind'] = 0;
            fundAmtDiv.slideUp();
        }
    },
    
    updateContactWarning: function(){
        if(this.model.get('personName').trim() == '' || this.model.get('position').trim() == ''){
            return false;
        } else {
            return true;
        }
    },
    
    updateDescriptionWarning: function(){
        if(this.model.get('other').trim() == ''){
            return false;
        } else {
            return true;
        }
    },
    
    updateCountryWarning: function(){
        if(this.model.get('country').trim() == '' || this.model.get('sector').trim() == ''){
            return false;
        } else {
            return true;
        }
    },
    
    updateExistedWarning: function(){
        if(this.model.get('knowledgeUser') != 1 && this.model.get('existed') == ""){
            return false;
        } 
        else {
            return true;
        }
    },
    
    updateFundsWarning: function(){
        if($("[name='fund']:checked").val().trim() == 'yes' && (this.model.get('cash') == "" || 
                                                                parseInt(this.model.get('cash')) == 0 || 
                                                                _.isNaN(parseInt(this.model.get('cash')))) && 
                                                               (this.model.get('inkind') == "" || 
                                                                parseInt(this.model.get('inkind')) == 0 || 
                                                                _.isNaN(parseInt(this.model.get('inkind'))))){
            return false;
        } 
        else {
            return true;
        }
    },
    
    updateThemesWarning: function(){
        if(this.model.get('knowledgeUser') != 1 &&
           this.model.get('planning') != "1" &&
           this.model.get('designDataCollection') != "1" &&
           this.model.get('analysisOfResults') != "1" &&
           this.model.get('exchangeKnowledge') != "1" &&
           this.model.get('userKnowledge') != "1"){
            return false;
        }
        else {
            return true;
        }
    },
    
    updateProjects: function(){
        if(this.model.get('projects').length == 0){
            return false;
        }
        else {
            return true;
        }
    },
    
    updateFiles: function(){
        var tooBig = false;
        _.each(this.model.get('files'), function(file){
            if(file.size > 1024*1024*2){
                tooBig = true;
            }
        });
        return !tooBig;
    },

    render: function(){
        var formType = this.model.getType();
        if(this.model.isNew()){
            main.set('title', 'New ' + formType);
        }
        else {
            main.set('title', 'Edit ' + formType);
        }
        this.$el.html(this.template(_.extend({formType:formType}, this.model.toJSON())));
        //this.$('[name=sector]').chosen({width: "400px"});
        this.$('[name=country]').chosen({width: "400px"});
        this.$('[name=number]').forceNumeric({max: 999, min: 0});
        return this.$el;
    },
});
