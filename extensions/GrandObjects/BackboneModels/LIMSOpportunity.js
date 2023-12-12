LIMSOpportunity = Backbone.Model.extend({

    toDelete: false,
    contact: null, // Parent obj

    initialize: function(){
        this.tasks = new LIMSTasks();
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
        return 'index.php?action=api.limsopportunity';
    },

    defaults: function() {
        return{
            id: null,
            requestId: "",
            contact: "",
            owner: {id: "",
                    name: "",
                    url: ""},
            project: {id: "",
                      name: "",
                      url: ""},
            userType: "",
            description: "",
            category: "",
            surveyed: "",
            responded: "",
            satisfaction: "",
            status: "",
            products: [],
            files: [],
            date: "",
            isAllowedToEdit: true
        };
    }

});

/**
 * LIMSOpportunities Collection
 */
LIMSOpportunities = Backbone.Collection.extend({
    model: LIMSOpportunity,
    
    contact: null,
    
    url: function(){
        return 'index.php?action=api.limscontact/' + this.contact.get('id') + '/limsopportunities';
    }
});
