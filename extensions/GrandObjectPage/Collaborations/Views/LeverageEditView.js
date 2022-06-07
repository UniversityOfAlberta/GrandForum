LeverageEditView = CollaborationEditView.extend({

    initialize: function(){
        this.parent = this;
        this.listenTo(this.model, "sync", this.fixFunding.bind(this));
        this.listenTo(this.model, "sync", this.render);
        this.listenTo(this.model, "change:title", function(){
            if(!this.isDialog){
                main.set('title', this.model.get('title'));
            }
        });
        this.fixFunding();
        
        this.template = _.template($('#leverage_edit_template').html());

        if(!this.model.isNew() && !this.isDialog){
            this.model.fetch();
        }
        else{
            _.defer(this.render);
        }
    },
    
    cancel: function(){
        if(this.model.get('id') > 0){
            document.location = this.model.get('url');
        }
        else{
            // Doesn't exist yet
            document.location = "#/leverages";
        }
    },
    
    fixFunding: function(){
        function roundMoney(number){
            if(Math.round(number) == number){
                return Math.round(number);
            }
            return number;
        }
        this.model.set('cash', roundMoney(this.model.get('cash')));
        this.model.set('projectedCash', roundMoney(this.model.get('projectedCash')));
        this.model.set('cashFY5', roundMoney(this.model.get('cashFY5')));
        this.model.set('cashFY6', roundMoney(this.model.get('cashFY6')));
        this.model.set('cashFY7', roundMoney(this.model.get('cashFY7')));
        this.model.set('cashFY8', roundMoney(this.model.get('cashFY8')));
        this.model.set('cashFY9', roundMoney(this.model.get('cashFY9')));
        this.model.set('cashFY10', roundMoney(this.model.get('cashFY10')));
        this.model.set('inkind', roundMoney(this.model.get('inkind')));
        this.model.set('projectedInkind', roundMoney(this.model.get('projectedInkind')));
        this.model.set('inkindFY5', roundMoney(this.model.get('inkindFY5')));
        this.model.set('inkindFY6', roundMoney(this.model.get('inkindFY6')));
        this.model.set('inkindFY7', roundMoney(this.model.get('inkindFY7')));
        this.model.set('inkindFY8', roundMoney(this.model.get('inkindFY8')));
        this.model.set('inkindFY9', roundMoney(this.model.get('inkindFY9')));
        this.model.set('inkindFY10', roundMoney(this.model.get('inkindFY10')));
    
        if(this.model.get('cash') == 0){
            this.model.set('cash', "");
        }
        if(this.model.get('projectedCash') == 0){
            this.model.set('projectedCash', "");
        }
        if(this.model.get('inkind') == 0){
            this.model.set('inkind', "");
        }
        if(this.model.get('projectedInkind') == 0){
            this.model.set('projectedInkind', "");
        }
    },
    
    updateFundsWarning: function(){
        function isValid(number){
            return (number == undefined || 
                    number == "" || 
                    parseInt(number) == 0 || 
                    !_.isNaN(parseInt(number)));
        }
        
        if(!isValid(this.model.get('cash')) ||
           !isValid(this.model.get('projectedCash')) ||
           !isValid(this.model.get('extra')['cashFY5']) ||
           !isValid(this.model.get('extra')['cashFY6']) ||
           !isValid(this.model.get('extra')['cashFY7']) ||
           !isValid(this.model.get('extra')['cashFY8']) ||
           !isValid(this.model.get('extra')['cashFY9']) ||
           !isValid(this.model.get('extra')['cashFY10']) ||
           !isValid(this.model.get('inkind')) ||
           !isValid(this.model.get('projectedInkind')) ||
           !isValid(this.model.get('extra')['inkindFY5']) ||
           !isValid(this.model.get('extra')['inkindFY6']) ||
           !isValid(this.model.get('extra')['inkindFY7']) ||
           !isValid(this.model.get('extra')['inkindFY8']) ||
           !isValid(this.model.get('extra')['inkindFY9']) ||
           !isValid(this.model.get('extra')['inkindFY10'])){
            return false;
        } 
        else {
            return true;
        }
    },
    
    saveCollaboration: function(){
        if (this.model.get("title").trim() == '') {
            clearWarning();
            addWarning('Organization name must not be empty', true);
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
            addWarning("This " + this.model.getType().toLowerCase() + " has invalid funding information (not in the form of a number)", true);
            return;
        }
        if(!this.updateFiles()){
            clearWarning();
            addWarning("The max file size is 2MB per file", true);
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
                    addError("There was a problem saving the Leverage", true);
                }
            }.bind(this)
        });
    }
    
});
