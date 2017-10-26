GsmsData = Backbone.Model.extend({

    initialize: function(){	
    },

    urlRoot: 'index.php?action=api.gsmsdata',

    defaults: function() {
        return{
            id:null,
	    review_status: null,
	    applicant_number: null,
	    gender: "",
	    data_of_birth: "",
	    program_name: "",
	    country_of_birth: "",
            country_of_citizenship: "",
            applicant_type: "",
            education_history: "",
            department: "",
            history: "",
            epl_test: "",
            epl_score: "",
            epl_listen: "",
            epl_write: "",
            epl_read: "",
            epl_speaking: "",
            additional: new Array(),
        };
    }

});

GsmsDataAll = Backbone.Collection.extend({

    model: GsmsData,

    search: '',

    url: function(){
        return 'index.php?action=api.gsmsdatas/';
    }

});
