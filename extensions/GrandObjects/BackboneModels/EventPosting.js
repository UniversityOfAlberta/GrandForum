EventPosting = Posting.extend({

    initialize: function(){
        
    },
    
    getWebsiteUrl: function(){
        return "https://cscan-infocan.ca/event/?article_id=" + this.get('id');
    },
    
    toggleLanguage: function(){
        if(this.get('language') == "English"){
            this.set('language', "French");
        } else {
            this.set('language', "English");
        }
    },

    urlRoot: 'index.php?action=api.eventposting',

    defaults: _.extend(Object.assign({}, Posting.prototype.defaults), {
        address: "",
        city: "",
        province: "",
        country: "Canada"
    })
    
});

EventPostings = Backbone.Collection.extend({
    
    model: EventPosting,
    
    url: 'index.php?action=api.eventposting' 
    
});
