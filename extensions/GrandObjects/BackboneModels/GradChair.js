GradChair = Backbone.Model.extend({

    initialize: function(){
        
    },
    
    idAttribute: "hqpId",

    urlRoot: 'index.php?action=api.gradchair',
    
    defaults: {
        hqpId: null,
        hqp: null,
        program: "",
        supervisors: null,
        background: "",
        background_notes: "",
        meetings: "",
        meetings_notes: "",
        ethics: "",
        ethics_notes: "",
        courses: "",
        courses_notes: "",
        notes: ""
    }
    
});

GradChairs = Backbone.Collection.extend({
    model: GradChair,
    
    url: 'index.php?action=api.gradchair'
});
