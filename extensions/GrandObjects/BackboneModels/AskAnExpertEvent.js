AskAnExpertEvent = Backbone.Model.extend({

    initialize: function(){

    },

    urlRoot: 'index.php?action=api.askanexpert',

    defaults: {
        id: null,
        name_of_expert: "",
        expert_field:"",
        date_of_event:"",
        end_of_event: "",
        active:true,
        date_created:"",
        currently_on:false,
        zoomlink:"",
        date_for_questions:"",
        theme: "",
        host: "",
        description: "",
        event: "",
        details: "",
        location: "",
    }
});

AskAnExpertEvents = Backbone.Collection.extend({
    model: AskAnExpertEvent,

    url: 'index.php?action=api.askanexpert/'
    
});
