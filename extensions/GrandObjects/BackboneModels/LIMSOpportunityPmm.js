LIMSOpportunityPmm = Backbone.Model.extend({

    toDelete: false,
    contact: null, // Parent obj

    initialize: function(){
        this.tasks = new LIMSTasksPmm();
        this.tasks.opportunity = this;
        if(!this.isNew()){
            this.tasks.fetch();
        }
        this.tasks.on("add", function(model){
            model.opportunity = this;
        }.bind(this));
        this.on("create", function(){
            this.tasks.each(function(task){
                task.set('opportunity', this.get('id'));
            }.bind(this));
        }.bind(this));
    },

    urlRoot: function(){
        return 'index.php?action=api.limsopportunitypmm';
    },

    defaults: function() {
        return{
            id: null,
            requestId: "",
            contact: "",
            owner: {id: "",
                    name: "",
                    url: ""},
            description: "",
            files: [],
            isAllowedToEdit: true
        };
    }

});

/**
 * LIMSOpportunities Collection
 */
LIMSOpportunitiesPmm = Backbone.Collection.extend({
    model: LIMSOpportunityPmm,
    
    contact: null,
    
    url: function(){
        return 'index.php?action=api.limscontactpmm/' + this.contact.get('id') + '/limsopportunitiespmm';
    }
});
