Person = Backbone.Model.extend({
    initialize: function(){
        this.bind("error", function(model, error){
            //addError(error);
        });
    },
    
    urlRoot: 'index.php?action=api.person',
    
    defaults: {
        id: null,
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
        department: '',
        publicProfile: '',
        privateProfile: ''
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

person2 = new Person({
    id: null,
    name: 'New.Name7',
    realName: 'New Name',
    reversedName: 'Name, New',
    email: 'dwt@ualberta.ca',
    nationality: 'Canadian',
    gender: '',
    photo: '',
    twitter: '',
    university: '',
    position: '',
    department: '',
    publicProfile: '',
    privateProfile: ''
});

person2.save();
