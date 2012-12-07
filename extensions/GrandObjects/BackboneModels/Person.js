Person = Backbone.Model.extend({
    initialize: function(){
        this.bind("error", function(model, error){
            addError(error);
        });
    },
    
    urlRoot: 'index.php?action=api.person',
    
    defaults: {
        id: 0,
        name: '',
        realName: '',
        reversedName: '',
        email: '',
        nationality: '',
        gender: '',
        photo: '',
        twitter: '',
        university: '',
        position: '',
        department: ''
    },
    
    validate: function(attr){
        if(attr.email == ''){
            return "Email address cannot be empty";
        }
        if(attr.id == ''){
            return "Id cannot be empty";
        }
        if(attr.name == ''){
            return "Name cannot be empty";
        }
        if(attr.gender != '' &&
           attr.gender != 'Male' &&
           attr.gender != 'Female'){
            return "Gender must be either Male/Female";
        }
        if(attr.nationality != '' &&
           attr.nationality != 'Canadian' &&
           attr.nationality != 'Landed Immigrant' &&
           attr.nationality != 'Visa Holder'){
            return "Nationality must be either Canadian/Landed Immigrant/Visa Holder";
        }
    }
});

var person = new Person({id : 3});
person.fetch({
    success: function(p){
        console.log(p.toJSON());
    }
});
