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
        projectId: 0,
        visibility: "Draft",
        jobTitle: "",
        deadlineType: "Hard",
        deadlineDate: "",
        startDateType: "No later than",
        startDate: "",
        tenure: "Yes",
        rank: "Full",
        rankOther: "",
        positionType: "Research + Teaching",
        researchFields: "",
        keywords: "",
        contact: "",
        sourceLink: "",
        summary: "",
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
