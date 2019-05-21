CollaborationEditView = Backbone.View.extend({

    isDialog: false,

    initialize: function(){
        this.parent = this;
        this.listenTo(this.model, "sync", this.render);
        this.listenTo(this.model, "change:personName", this.updateContactWarning);
        this.listenTo(this.model, "change:position", this.updateContactWarning);
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
        document.location = this.model.get('url');
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
        this.model.attributes['funding'] = $('input[name=funding]').val();
        if (funded == "yes") {
            fundAmtDiv.slideDown();
        } else {
            this.model.attributes['funding'] = 0;
            fundAmtDiv.slideUp();
        }
    },
    
    updateContactWarning: function(){
        if(this.contactWarning != null){
            if(this.model.get('personName').trim() == '' || this.model.get('position').trim() == ''){
                this.contactWarning.show();
            } else {
                this.contactWarning.hide();
            }
        }
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
        this.contactWarning = this.$("#contactWarning");
        this.$('[name=sector]').chosen({width: "400px"});
        this.$('[name=country]').chosen({width: "400px"});
        this.updateContactWarning();
        return this.$el;
    },
});
