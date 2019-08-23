Posting = Backbone.Model.extend({

    defaults: {
        id: null,
        userId: "",
        visibility: "Draft",
        language: "English",
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
        created: "",
        deleted: false,
        isAllowedToEdit: true,
        url: ""
    }
});

Postings = Backbone.Collection.extend({
    
    model: Posting
    
});
