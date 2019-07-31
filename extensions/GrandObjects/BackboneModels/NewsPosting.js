NewsPosting = Backbone.Model.extend({

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

    defaults: {
        id: null,
        translatedId: 0,
        userId: "",
        visibility: "Draft",
        language: "English",
        bilingual: "No",
        title: "",
        articleLink: "",
        postedDate: new Date().toLocaleDateString(),
        summary: "",
        author: "CS-Can/Info-Can",
        sourceName: "",
        sourceLink: "",
        image: "",
        imageCaption: "",
        created: "",
        deleted: false,
        isAllowedToEdit: true,
        url: ""
    }
});

NewsPostings = Backbone.Collection.extend({
    
    model: NewsPosting,
    
    url: 'index.php?action=api.newsposting' 
    
});
