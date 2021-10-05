ElitePosting = Posting.extend({

    initialize: function(){
        
    },

    urlRoot: 'index.php?action=api.eliteposting',

    defaults: function(){
        return _.extend(Object.assign({}, Posting.prototype.defaults), {
            visibility: "Submitted",
            type: "Intern",
            extra: {
                level: "Any Level"
            },
            comments: "",
        });
    }
    
});

ElitePostings = Backbone.Collection.extend({
    
    model: ElitePosting,
    
    type: "Intern",
    
    deleted: false,
    
    url: function(){
        if(this.deleted){
            return 'index.php?action=api.eliteposting/deleted';
        }
        else if(this.type == "Intern" || this.type == "PhD"){
            return 'index.php?action=api.eliteposting/' + this.type.toLowerCase();
        }
        else {
            return 'index.php?action=api.eliteposting';
        }
    }
    
});
