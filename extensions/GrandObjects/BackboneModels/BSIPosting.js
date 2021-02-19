BSIPosting = Posting.extend({

    initialize: function(){
        
    },

    urlRoot: 'index.php?action=api.bsiposting',

    defaults: function(){
        return _.extend(Object.assign({}, Posting.prototype.defaults), {
            type: "",
            partnerName: "",
            city: "",
            province: "",
            country: "",
            firstName: "",
            lastName: "",
            email: "",
            positions: "",
            positionsText: "",
            discipline: "",
            about: "",
            skills: "",
            deletedText: ""
        });
    }
    
});

BSIPostings = Backbone.Collection.extend({
    
    model: BSIPosting,
    
    deleted: false,
    
    url: function(){
        if(this.deleted){
            return 'index.php?action=api.bsiposting/deleted';
        }
        else {
            return 'index.php?action=api.bsiposting';
        }
    }
    
});
