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
        country: "Canada",
        website: "",
        image1: "", // For Uploads
        image2: "", // For Uploads
        image3: "", // For Uploads
        banner4: "", // For Uploads
        banner5: "", // For Uploads
        image_url1: "", // For Uploads
        image_url2: "", // For Uploads
        image_url3: "", // For Uploads
        image_url4: "", // For Uploads
        image_url5: "", // For Uploads
        image_delete1: "", // For Uploads
        image_delete2: "", // For Uploads
        image_delete3: "", // For Uploads
        image_delete4: "", // For Uploads
        image_delete5: "", // For Uploads
        images: []
    })
    
});

EventPostings = Backbone.Collection.extend({
    
    model: EventPosting,
    
    url: 'index.php?action=api.eventposting' 
    
});
