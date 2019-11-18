Alumni = Backbone.Model.extend({

    initialize: function(){
    },

    personId: null,

    urlRoot: function(){
        if(this.personId != null){
            return 'index.php?action=api.person/' + this.personId + '/alumni';
        }
        return 'index.php?action=api.alumni';
    },

    defaults: function() {
        return{
            id: null,
            user_id: "",
            recruited: "",
            recruited_country: "",
            alumni: "",
            alumni_country: "",
            alumni_sector: ""
        };
    }

});
