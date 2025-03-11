LIMSContactPmm = Backbone.Model.extend({

    toDelete: false,

    initialize: function(){
        this.opportunities = new LIMSOpportunitiesPmm();
        this.opportunities.contact = this;
        //if(!this.isNew()){
            this.once("sync", function(){
                this.opportunities.fetch();
            }.bind(this));
        //}
        this.opportunities.on("add", function(model){
            model.contact = this;
        }.bind(this));
        this.on("create", function(){
            this.opportunities.each(function(opportunity){
                opportunity.set('contact', this.get('id'));
            }.bind(this));
        }.bind(this));
        this.on("change:details", function(){
            /*var title = (this.get('details')['firstName'] || '').trim() + ' ' + (this.get('details')['lastName'] || '').trim();
            if(this.get('details')['institution'] != '' && this.get('details')['firstName'].trim() != ""){
                title += " (" +  this.get('details')['institution'].trim() + ")";           
            }*/
            this.set('title', '');
        }.bind(this));
    },

    urlRoot: function(){
        if(this.get('id') == null){
            return 'index.php?action=api.limscontactpmm/project/' + this.get('projectId');
        }
        return 'index.php?action=api.limscontactpmm';
    },

    defaults: function() {
        return{
            id: null,
            title: "",
            owner: "",
            projectId: "",
            details: {},
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
        return 'index.php?action=api.limscontactpmm';
    }
});
