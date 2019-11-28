Collaboration = Backbone.Model.extend({

    initialize: function(){
    },

    urlRoot: 'index.php?action=api.collaboration',

    getType: function() {
        return (this.get('knowledgeUser') == 0) ? "Collaboration" : "Knowledge User";
    },

    defaults: function() {
        return{
            id: null,
            title: "",
            creator: {},
            personName: "",
            position: "",
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
            existed: "",
            year: YEAR,
            endYear: "0",
            knowledgeUser: 0, // true or false
            changed: ""
        };
    }

});

Collaborations = Backbone.Collection.extend({

    model: Collaboration,
    
    person: null,

    url: function(){
        if(this.person == null){
            return 'index.php?action=api.collaboration';
        }
        /*
        else{
            return 'index.php?action=api.collaboration/person/' + this.person;
        }
        */
    }

});
