InfoSheet = Backbone.Model.extend({

    initialize: function(){	
    },

    urlRoot: 'index.php?action=api.infosheet',

    defaults: function() {
        return{
	    id: null,
	    user_id: null,
	    gpa60: "",
	    gpafull: "",
	    gpafull_credits: "",
	    gpafull_credits2: "",
            notes: "",
            anatomy: "",
            stats: "",
            degree: "",
            institution: "",
            failures: "",
            withdrawals: "",
            canadian: "",
            international: "",
            indigenous: "",
            saskatchewan: "",
            degrees: new Array(),
        };
    }

});
