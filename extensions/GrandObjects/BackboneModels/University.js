University = Backbone.Model.extend({

    initialize: function(){
        
    },

    urlRoot: 'index.php?action=api.university',

    defaults: {
        id: null,
        name: "",
        latitude: "",
        longitude: "",
        color: "",
        order: 10000,
        default: false,
        province: "",
        phone: "",
        hours:""
    }
});

Universities = Backbone.Collection.extend({
    model: University,
    
    url: function(){
	var url = 'index.php?action=api.university/';
	if(this.lat != null && this.long != null){
	    url = 'index.php?action=api.university/'+this.lat+'/'+this.long;
	}
	return url;
    }
});
