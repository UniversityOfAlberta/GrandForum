Posting = Backbone.Model.extend({

    defaults: {
        id: null,
        userId: "",
        visibility: "Draft",
        language: "English",
        bilingual: "No",
        title: "",
        articleLink: "",
        startDate: new Date().toLocaleDateString(),
        endDate: "",
        summary: "",
        image: "",
        imageCaption: "",
        created: "",
        deleted: false,
        isAllowedToEdit: true,
        url: ""
    }
});

Postings = Backbone.Collection.extend({
    
    model: Posting
    
});
