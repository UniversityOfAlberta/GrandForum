AvoidResource = Backbone.Model.extend({

    initialize: function(){
        
    },

    urlRoot: 'index.php?action=api.avoidresource',

    defaults: {
        id: null,
        ResourceAgencyNum: null,
        Split:"",
        PublicName:"",
        Category: "",
        SubCategory: "",
        SubSubCategory: "",
        PhysicalAddress1: "",
        PhysicalCity: "",
        WebsiteAddress: "",
        AgencyDescription: "",
        Eligibility:"",
        TaxonomyTerms:"",
        //given from xls only
        ParentAgency:"",
        PublicName_Program:"",
        HoursOfOperation:"",
        LanguagesOffered:"",
        LanguagesOfferedList:"",
        ApplicationProcess:"",
        Coverage:"",
        CoverageAreaText:"",
        PhysicalAddress2:"",
        PhysicalStateProvince:"",
        PhysicalPostalCode:"",
        MailingAttentionName:"",
        MailingAddress1:"",
        MailingAddress2:"",
        MailingCity:"",
        MailingStateProvince:"",
        MailingPostalCode:"",
        DisabilitiesAccess:"",
        Phone1Name:"",
        Phone1Number:"",
        Phone1Description:"",
        PhoneNumberBusinessLine:"",
        PhoneTollFree:"",
        PhoneFax:"",
        EmailAddressMain:"",
        Custom_Facebook:"",
        Custom_Instagram:"",
        Custom_LinkedIn:"",
        Custom_Twitter:"",
        Custom_YouTube:"",
        Categories:"",
        LastVerifiedOn:"",
	lat:"",
	lon: "",
    }
});

AvoidResources = Backbone.Collection.extend({
       model: AvoidResource,

    url: function(){
         url = 'index.php?action=api.avoidResource/';
	         url = 'index.php?action=api.callAvoidResourcesApi/';

        if(this.cat != null){
            url = 'index.php?action=api.avoidResources/'+this.cat;
		url = 'index.php?action=api.callAvoidResourcesApi/&cat='+this.cat;

        }
	else if(this.key != null){
		url='index.php?action=api.callAvoidResourcesApi/&key='+this.key;;

	}

        return url;
    }
}); 
