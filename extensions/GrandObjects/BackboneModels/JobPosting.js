JobPosting = Backbone.Model.extend({

    initialize: function(){
        
    },
    
    getWebsiteUrl: function(){
        return "https://cscan-infocan.ca/careers/?job_id=" + this.get('id');
    },

    urlRoot: 'index.php?action=api.jobposting',

    defaults: {
        id: null,
        userId: "",
        user: null,
        projectId: 0,
        project: null,
        visibility: "Draft",
        jobTitle: "",
        jobTitleFr: "",
        deadlineType: "Hard",
        deadlineDate: "",
        startDateType: "No later than",
        startDate: "",
        tenure: "Yes",
        rank: "Full Professor",
        rankOther: "",
        language: "en",
        positionType: "Research + Teaching",
        researchFields: "",
        researchFieldsFr: "",
        keywords: "",
        keywordsFr: "",
        contact: "",
        sourceLink: "",
        summary: "",
        summaryFr: "",
        previewCode: "",
        created: "",
        deleted: false,
        department: "",
        university: "",
        isAllowedToEdit: true,
        url: ""
    }
});

JobPostings = Backbone.Collection.extend({
    
    model: JobPosting,
    
    url: 'index.php?action=api.jobposting' 
    
});
