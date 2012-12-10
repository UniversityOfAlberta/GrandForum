Person = Backbone.RelationalModel.extend({
    initialize: function(){
        this.bind("error", function(model, error){
            //addError(error);
        });
    },
    
    relations: [{
        type: Backbone.HasMany,
        key: 'projects',
        relatedModel: 'Project',
        collectionType: 'Projects',
        reverseRelation: {
            key: 'isMemberOf',
            includeInJSON: 'id'
        }
    }],
    
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
    
    /*
    validate: function(attr){
        if(attr.email == ''){
            return "Email address cannot be empty";
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
    */
});

People = Backbone.Collection.extend({
    model: Person,
    
    url: 'index.php?action=api.person'
});
