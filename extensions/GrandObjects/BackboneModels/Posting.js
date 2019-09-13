Posting = Backbone.Model.extend({

    getLanguage: function(){
        if(this.get('language') == "en"){
            return "English";
        }
        else if(this.get('language') == "fr"){
            return "Français";
        }
        else if(this.get('language') == "en"){
            return "Bilingual/Bilingue";
        }
        else{
            return this.get('language');
        }
    },

    defaults: {
        id: null,
        userId: "",
        visibility: "Draft",
        language: "en",
        title: "",
        titleFr: "",
        articleLink: "",
        startDate: $.datepicker.formatDate("yy-mm-dd", new Date()),
        endDate: "",
        summary: "",
        summaryFr: "",
        image: "",
        imageCaption: "",
        imageCaptionFr: "",
        previewCode: "",
        created: "",
        deleted: false,
        isAllowedToEdit: true,
        url: ""
    }
});

Postings = Backbone.Collection.extend({
    
    model: Posting
    
});
