LIMSContactPmm = Backbone.Model.extend({

    toDelete: false,

    initialize: function(){
        this.opportunities = new LIMSOpportunities();
        this.opportunities.contact = this;
        if(!this.isNew()){
            this.once("sync", function(){
                this.opportunities.fetch();
            }.bind(this));
        }
        this.opportunities.on("add", function(model){
            model.contact = this;
        }.bind(this));
        this.on("create", function(){
            this.opportunities.each(function(opportunity){
                opportunity.set('contact', this.get('id'));
            }.bind(this));
        }.bind(this));
        this.on("change:details", function(){
            var title = (this.get('details')['firstName'] || '').trim() + ' ' + (this.get('details')['lastName'] || '').trim();
            if(this.get('details')['institution'] != '' && this.get('details')['firstName'].trim() != ""){
                title += " (" +  this.get('details')['institution'].trim() + ")";           
            }
            this.set('title', title);
        }.bind(this));
    },

    urlRoot: function(){
        return 'index.php?action=api.limscontact';
    },

    defaults: function() {
        return{
            id: null,
            title: "",
            owner: "",
            details: {firstName: "",
                      lastName: "",
                      email: "",
                      institution: ""},
            projects: new Array(),
            url: "",
            isAllowedToEdit: true
        };
    }

});

/**
 * LIMSContacts Collection
 */
LIMSContactsPmm = Backbone.Collection.extend({
    model: LIMSContactPmm,
    
    url: function(){
        return 'index.php?action=api.limscontact';
    }
});
