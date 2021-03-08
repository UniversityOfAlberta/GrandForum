CRMOpportunity = Backbone.Model.extend({

    toDelete: false,
    contact: null, // Parent obj

    initialize: function(){
        this.tasks = new CRMTasks();
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
        return 'index.php?action=api.crmopportunity';
    },

    defaults: function() {
        return{
            id: null,
            contact: "",
            owner: {id: "",
                    name: "",
                    url: ""},
            description: "",
            category: "",
            isAllowedToEdit: true
        };
    }

});

/**
 * CRMOpportunities Collection
 */
CRMOpportunities = Backbone.Collection.extend({
    model: CRMOpportunity,
    
    contact: null,
    
    url: function(){
        return 'index.php?action=api.crmcontact/' + this.contact.get('id') + '/crmopportunities';
    }
});
