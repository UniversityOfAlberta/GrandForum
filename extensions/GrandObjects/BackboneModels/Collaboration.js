Collaboration = Backbone.Model.extend({

    initialize: function(){
    },

    urlRoot: 'index.php?action=api.collaboration',

    getType: function() {
        if(this.get('leverage') == 1){ return "Leverage"; }
        if(this.get('knowledgeUser') == 1){ return "Knowledge User"; }
        if(this.get('knowledgeUser') == 0){ return "Collaboration"; }
    },

    defaults: function() {
        return{
            id: null,
            title: "",
            creator: {},
            personName: "",
            position: "",
            email: "",
            projects: new Array(),
            url: "",
            sector: "",
            country: "",
            planning: "",
            designDataCollection: "",
            analysisOfResults: "",
            exchangeKnowledge: "",
            userKnowledge: "",
            other: "",
            cash: 0,
            inkind: 0,
            projectedCash: 0,
            projectedInkind: 0,
            existed: "",
            year: YEAR,
            endYear: "0",
            fileCount: 0,
            files: new Array(),
            knowledgeUser: 0, // true or false
            leverage: 0, // true or false
            changed: ""
        };
    }

});

Collaborations = Backbone.Collection.extend({

    model: Collaboration,
    
    person: null,
    
    leverages: false,

    url: function(){
        if(this.leverages == 0){
            return 'index.php?action=api.collaboration';
        }
        else{
            return 'index.php?action=api.collaboration/leverages';
        }
    }

});
