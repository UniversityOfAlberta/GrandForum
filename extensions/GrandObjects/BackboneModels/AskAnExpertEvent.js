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
	active:false,
	date_created:"",
	currently_on:false,
	zoomlink:"",
	date_for_questions:"",
	theme: "",
	host: "",
	description: "",
	evnt: "",
	details: "",
	locate: "",
    }
});

AskAnExpertEvents = Backbone.Collection.extend({
       model: AskAnExpertEvent,

    url: function(){
        url = 'index.php?action=api.askanexpert/';
        if(this.get('id') != null){
                url = 'index.php?action=api.askanexpert/'+this.get('id');

        }
        return url;
    }
});
