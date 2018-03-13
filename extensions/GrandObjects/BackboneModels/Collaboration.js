Collaboration = Backbone.Model.extend({

    initialize: function(){
    },

    urlRoot: 'index.php?action=api.collaboration',

    defaults: function() {
        return{
            id: null,
            title: "",
            url: "",
            person: null,
            editors: new Array(),
            products: new Array(),
            thread_id: null,
            sector: "",
            country: "",
            planning: "",
            designDataCollection: "",
            analysisOfResults: "",
            exchangeKnowledge: "",
            userKnowledge: "",
            other: "",
            funding: 0,
            year: YEAR,
            knowledgeUser: 0 // true or false
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
