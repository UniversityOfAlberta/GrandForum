NewsPosting = Posting.extend({

    initialize: function(){
        
    },
    
    getWebsiteUrl: function(){
        return "https://cscan-infocan.ca/news/?article_id=" + this.get('id');
    },
    
    toggleLanguage: function(){
        if(this.get('language') == "English"){
            this.set('language', "French");
        } else {
            this.set('language', "English");
        }
    },

    urlRoot: 'index.php?action=api.newsposting',

    defaults: _.extend(Object.assign({}, Posting.prototype.defaults), {
        author: "",
        sourceName: "",
        sourceLink: "",
        enableRegistration: false,
        enableMaterials: false
    })
    
});

NewsPostings = Backbone.Collection.extend({
    
    model: NewsPosting,
    
    url: 'index.php?action=api.newsposting' 
    
});
