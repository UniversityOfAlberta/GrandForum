CRMOpportunity = Backbone.Model.extend({

    initialize: function(){
        this.tasks = new CRMTasks();
        this.tasks.opportunity = this;
        if(!this.isNew()){
            this.tasks.fetch();
        }
        
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
            description: "",
            category: ""
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
