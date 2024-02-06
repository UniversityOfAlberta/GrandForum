AskAnExpertEvent = Backbone.Model.extend({

    initialize: function(){
        
    },

    urlRoot: 'index.php?action=api.askanexpert',

    toJSON: function(){
        var data = Backbone.Model.prototype.toJSON.call(this);
        if(this.isNew()){
            return data;
        }
        var locale = (wgLang == 'en') ? 'en-US' : 'fr-CA';
        if(data["date_of_event"] != null){
            var origDate = new Date(data["date_of_event"].replace(/-/g, "/"));
            var split = data["date_of_event"].split(" ");
            var parts = split[0].split('-');
            var date = new Date(parts[0], parts[1] - 1, parts[2]);
            var timesplit = split[1].split(":");
            var time = timesplit[0] + ":" + timesplit[1];
            data["date"] = date.toLocaleDateString(locale, { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' });
            data["time"] = origDate.toLocaleString(locale, { hour: 'numeric', hour12: true, minute: 'numeric' });
        }
        if(data["date_for_questions"] != null){
            var split = data["date_for_questions"].split(" ");
            var parts = split[0].split('-');
            var date = new Date(parts[0], parts[1] - 1, parts[2]);
            var timesplit = split[1].split(":");
            var time = timesplit[0] + ":" + timesplit[1];
            data["date_for_questions"] = date.toLocaleDateString(locale, { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' });
        }
        if(data["end_of_event"] != null){
            var origDate = new Date(data["end_of_event"].replace(/-/g, "/"));
            var split = data["end_of_event"].split(" ");
            var parts = split[0].split('-');
            var date = new Date(parts[0], parts[1] - 1, parts[2]);
            var timesplit = split[1].split(":");
            var time = timesplit[0] + ":" + timesplit[1];
            data["end_date"] = date.toLocaleDateString(locale, { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' });
            data["end_time"] = origDate.toLocaleString(locale, { hour: 'numeric', hour12: true, minute: 'numeric' });
        }
        return data;
    },

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
        location: ""
    }
});

AskAnExpertEvents = Backbone.Collection.extend({
    model: AskAnExpertEvent,

    url: 'index.php?action=api.askanexpert/'
    
});
