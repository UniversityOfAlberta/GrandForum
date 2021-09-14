ElitePosting = Posting.extend({

    initialize: function(){
        
    },

    urlRoot: 'index.php?action=api.eliteposting',

    defaults: function(){
        return _.extend(Object.assign({}, Posting.prototype.defaults), {
            visibility: "Submitted",
            companyName: "",
            companyProfile: "",
            reportsTo: "",
            basedAt: "",
            training: "",
            responsibilities: "",
            qualifications: "",
            skills: "",
            level: "Any Level",
            positions: "",
            comments: "",
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
