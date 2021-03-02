GsmsData = Backbone.Model.extend({

    initialize: function(){ 
    },

    url: function(){
        var url = 'index.php?action=api.gsmsdata/' + this.get('user_id');
        if(this.get('year') != ''){
            url += '/' + this.get('year');
        }
        return url;
    },
    
    idAttribute: 'user_id',

    defaults: function() {
        return {
            id:null,
            ois_id: "",
            review_status: null,
            applicant_number: null,
            gender: "",
            year: "",
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
            gsms_url: "",
            submitted_date: "",
            hidden: false,
            favorited: false,
            sop_url: "",
            sop_pdf: ""
        };
    }

});

GsmsDataAll = Backbone.Collection.extend({

    model: GsmsData,

    folder: '',
    
    program: 'all',
    
    decision: 'all',

    year: '',

    search: '',

    url: function(){
        var url = 'index.php?action=api.gsmsdatas/' + this.folder + '/' + this.program + '/' + this.decision;
        if(this.year != ''){
            url += '/' + this.year;
        }
        return url;
    }

});
