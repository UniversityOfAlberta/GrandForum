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

    getAdditional: function(field, def, decimals){
        if(def == undefined){
            def = "";
        }
        var fields = field.split(".");
        var additional = this.get('additional');
        for(var i=0; i < fields.length; i++){
            field = fields[i];
            if(additional[field] != undefined &&
               additional[field] != ""){
                var val = additional[field];
                if(!_.isArray(val) && !_.isObject(val)){
                    if(decimals != undefined &&
                       (_.isNumber(val) || (_.isString(val) && val.match(/[0-9\.]+/)))){
                        val = parseFloat(val);
                        return val.toFixed(decimals);
                    }
                    return val;
                }
                else{
                    additional = val;
                    if(i == fields.length - 1){
                        return val;
                    }
                }
            }
        }
        return def;
    },

    defaults: function() {
        return {
            id:null,
            student_data: {id: 0, name: "", url: ""},
            ois_id: "",
            year: "",
            history: "",
            additional: new Array(),
            gsms_url: "",
            sop_url: "",
            sop_pdf: "",
            annoations: new Array()
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
