ElitePosting = Posting.extend({

    initialize: function(){
        
    },

    urlRoot: 'index.php?action=api.eliteposting',

    defaults: function(){
        return _.extend(Object.assign({}, Posting.prototype.defaults), {
            companyName: "",
            companyProfile: "",
            reportsTo: "",
            basedAt: "",
            responsibilities: "",
            qualifications: "",
            skills: ""
        });
    }
    
});

ElitePostings = Backbone.Collection.extend({
    
    model: ElitePosting,
    
    deleted: false,
    
    url: function(){
        if(this.deleted){
            return 'index.php?action=api.eliteposting/deleted';
        }
        else {
            return 'index.php?action=api.eliteposting';
        }
    }
    
});
