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

    getAdditional: function(field, def){
        if(def == undefined){
            def = "";
        }
        if(this.get('additional')[field] != undefined &&
           this.get('additional')[field] != ""){
            return this.get('additional')[field];
        }
        return def;
    },

    defaults: function() {
        return {
            id:null,
            ois_id: "",
            year: "",
            history: "",
            additional: new Array(),
            gsms_url: "",
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
