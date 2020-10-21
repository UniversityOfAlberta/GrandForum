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
            skills: ""
        });
    }
    
});

BSIPostings = Backbone.Collection.extend({
    
    model: BSIPosting,
    
    url: 'index.php?action=api.bsiposting' 
    
});
