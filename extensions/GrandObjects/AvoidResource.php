<?php

/**
 * @package GrandObjects
 */

class AvoidResource extends BackboneModel {
    
    static $cache = array();
    
    //from API stuff
    var $id;
    var $Split;
    var $PublicName;
    var $Category;
    var $SubCategory;
    var $SubSubCategory;
    var $TaxonomyTerms;
    //both
    var $WebsiteAddress;
    var $ResourceAgencyNum;
    var $PhysicalAddress1;
    var $PhysicalCity;
    var $PhysicalCounty;
    var $AgencyDescription;

    //given from xls only
    var $ParentAgency;
    var $PublicName_Program;
    var $HoursOfOperation;
    var $LanguagesOffered;
    var $LanguagesOfferedList;
    var $ApplicationProcess;
    var $Coverage;
    var $CoverageAreaText;
    var $PhysicalAddress2;
    var $PhysicalStateProvince;
    var $PhysicalPostalCode;
    var $MailingAttentionName;
    var $MailingAddress1;
    var $MailingAddress2;
    var $MailingCity;
    var $MailingStateProvince;
    var $MailingPostalCode;
    var $DisabilitiesAccess;
    var $Phone1Name;
    var $Phone1Number;
    var $Phone1Description;
    var $PhoneNumberBusinessLine;
    var $PhoneTollFree;
    var $PhoneFax;
    var $EmailAddressMain;
    var $Custom_Facebook;
    var $Custom_Instagram;
    var $Custom_LinkedIn;
    var $Custom_Twitter;
    var $Custom_YouTube;
    var $Categories;
    var $LastVerifiedOn;

    
    static function newFromId($id){
        if(isset($cache[$id])){
            return $cache[$id];
        }
        $data = DBFunctions::select(array('grand_avoid_resources'),
                                    array('*'),
                                    array('`id`' => EQ($id),
                                          ));
        $avoidresource = new AvoidResource($data);
        $cache[$id] = $avoidresource;
        return $avoidresource;
    }
    
    static function newFromName($name){
        if(isset($cache[$name])){
            return $cache[$name];
        }
        $data = DBFunctions::select(array('grand_avoid_resources'),
                                    array('*'),
                                    array('`PublicName`' => EQ($id),
                                          ));
        $avoidresource = new AvoidResource($data);
        $cache[$name] = $avoidresource;
        return $avoidresource;
    }
    
    static function getAllAvoidResources(){
        $sql = "SELECT * FROM `grand_avoid_resources`";
        $data = DBFunctions::execSQL($sql);
        $unis = array();
        foreach($data as $row){
            $unis[] = AvoidResource::newFromId($row['id']);
        }
        return $unis;

    }

    //idk how to do this with no long lat in data prob city only?
    // static function getNearestUniversity($lat, $long){
    //     $sql = "SELECT * , SQRT( POW( ABS( latitude - ($lat)) , 2 ) + POW( ABS( longitude -($long)) , 2 ) ) AS dist
    //         FROM `grand_universities` WHERE `university_name` <> 'Unknown' AND `longitude` <> 'NULL' AND `latitude` <> 'NULL'
    //         ORDER BY `dist` ASC LIMIT 10";
    //     $data = DBFunctions::execSQL($sql);
    //     $unis = array();
    //     foreach($data as $row){
    //         $unis[] = University::newFromId($row['university_id']);
    //     }
    //     return $unis;
    // }

    static function getCategoryResources($cat){
        //$sql = "SELECT `alias_database_name` FROM `grand_avoid_categories` WHERE `name` = '$cat'";
	//$data = DBFunctions::execSQL($sql);
	//$category = $data[0]['alias_database_name'];
	$sql = "SELECT * FROM `grand_avoid_resources` WHERE `Categories` LIKE '%$cat%'";
	$data = DBFunctions::execSQL($sql);
        $unis = array();
        foreach($data as $row){
            $unis[] = AvoidResource::newFromId($row['id']);
        }
        return $unis;
    }



    function __construct($data){
        if(count($data) > 0){
            $row = $data[0];
            $this->ResourceAgencyNum = $row['ResourceAgencyNum'];
            $this->Split = $row['Split'];
            $this->PublicName = $row['PublicName'];
            $this->Category = $row['Category'];
            $this->SubCategory = $row['SubCategory'];
            $this->SubSubCategory = $row['SubSubCategory'];
            $this->PhysicalAddress1 = $row['PhysicalAddress1'];
            $this->PhysicalCity = $row['PhysicalCity'];
            $this->PhysicalCounty = $row['PhysicalCounty'];
            $this->WebsiteAddress = $row['WebsiteAddress'];
            $this->AgencyDescription = $row['AgencyDescription'];
            $this->Eligibility = $row['Eligibility'];
            $this->TaxonomyTerms = $row['TaxonomyTerms'];

            $this->ParentAgency = $row['ParentAgency'];
            $this->PublicName_Program = $row['PublicName_Program'];
            $this->HoursOfOperation = $row['HoursOfOperation'];
            $this->LanguagesOffered = $row['LanguagesOffered'];
            $this->LanguagesOfferedList = $row['LanguagesOfferedList'];
            $this->ApplicationProcess = $row['ApplicationProcess'];
            $this->Coverage = $row['Coverage'];
            $this->CoverageAreaText = $row['CoverageAreaText'];
            $this->PhysicalAddress2 = $row['PhysicalAddress2'];
            $this->PhysicalStateProvince = $row['PhysicalStateProvince'];
            $this->PhysicalPostalCode = $row['PhysicalPostalCode'];
            $this->MailingAttentionName = $row['MailingAttentionName'];
            $this->MailingAddress1 = $row['MailingAddress1'];
            $this->MailingAddress2 = $row['MailingAddress2'];
            $this->MailingCity = $row['MailingCity'];
            $this->MailingStateProvince = $row['MailingStateProvince'];
            $this->MailingPostalCode = $row['MailingPostalCode'];
            $this->DisabilitiesAccess = $row['DisabilitiesAccess'];
            $this->Phone1Name = $row['Phone1Name'];
            $this->Phone1Number = $row['Phone1Number'];
            $this->Phone1Description = $row['Phone1Description'];
            $this->PhoneNumberBusinessLine = $row['PhoneNumberBusinessLine'];
            $this->PhoneTollFree = $row['PhoneTollFree'];
            $this->PhoneFax = $row['PhoneFax'];
            $this->EmailAddressMain = $row['EmailAddressMain'];
            $this->Custom_Facebook = $row['Custom_Facebook'];
            $this->Custom_Instagram = $row['Custom_Instagram'];
            $this->Custom_LinkedIn = $row['Custom_LinkedIn'];
            $this->Custom_Twitter = $row['Custom_Twitter'];
            $this->Custom_YouTube = $row['Custom_YouTube'];
            $this->Categories = $row['Categories'];
            $this->LastVerifiedOn = $row['LastVerifiedOn'];


        }
    }
    
    function toArray(){
        global $wgUser;
        $json = array(
                    'ResourceAgencyNum' => $this->getResourceAgencyNum(),
                    'Split' => $this->getSplit(),
                    'PublicName' => $this->getPublicName(),
                    'Category' => $this->getCategory(),
                    'SubCategory' => $this->getSubCategory(),
                    'SubSubCategory' => $this->getSubSubCategory(),
                    'PhysicalAddress1' => $this->getPhysicalAddress1(),
                    'PhysicalCity' => $this->getPhysicalCity(),
                    'PhysicalCounty'=> $this->getPhysicalCounty(),
                    'WebsiteAddress'=> $this->getWebsiteAddress(),
                    'AgencyDescription' => $this->getAgencyDescription(),
                    'Eligibility'=> $this->getEligibility(),
                    'TaxonomyTerms'=> $this->getTaxonomyTerms(),

                    'ParentAgency'=> $this->ParentAgency,
                    'PublicName_Program'=> $this->PublicName_Program,
                    'HoursOfOperation'=> $this->HoursOfOperation,
                    'LanguagesOffered'=> $this->LanguagesOffered,
                    'LanguagesOfferedList'=> $this->LanguagesOfferedList,
                    'ApplicationProcess'=> $this->ApplicationProcess,
                    'Coverage'=> $this->Coverage,
                    'CoverageAreaText'=> $this->CoverageAreaText,
                    'PhysicalAddress2'=> $this->PhysicalAddress2,
                    'PhysicalStateProvince'=> $this->PhysicalStateProvince,
                    'PhysicalPostalCode'=> $this->PhysicalPostalCode,
                    'MailingAttentionName'=> $this->MailingAttentionName,
                    'MailingAddress1'=> $this->MailingAddress1,
                    'MailingAddress2'=> $this->MailingAddress2,
                    'MailingCity'=> $this->MailingCity,
                    'MailingStateProvince'=> $this->MailingStateProvince,
                    'MailingPostalCode'=> $this->MailingPostalCode,
                    'DisabilitiesAccess'=> $this->DisabilitiesAccess,
                    'Phone1Name'=> $this->Phone1Name,
                    'Phone1Number'=> $this->Phone1Number,
                    'Phone1Description'=> $this->Phone1Description,
                    'PhoneNumberBusinessLine'=> $this->PhoneNumberBusinessLine,
                    'PhoneTollFree'=> $this->PhoneTollFree,
                    'PhoneFax'=> $this->PhoneFax,
                    'EmailAddressMain'=> $this->EmailAddressMain,
                    'Custom_Facebook'=> $this->Custom_Facebook,
                    'Custom_Instagram'=> $this->Custom_Instagram,
                    'Custom_LinkedIn'=> $this->Custom_LinkedIn,
                    'Custom_Twitter'=> $this->Custom_Twitter,
                    'Custom_YouTube'=> $this->Custom_YouTube,
                    'Categories'=> $this->Categories,
                    'LastVerifiedOn'=> $this->LastVerifiedOn,
                );
        return $json;
    }
    
    function create(){
            $me = Person::newFromWgUser();
            if($me->isRoleAtLeast(EXTERNAL)){
                DBFunctions::begin();
                $status = DBFunctions::insert('grand_universities',
                                            array(
                                            'ResourceAgencyNum' => $this->getResourceAgencyNum(),
                                            'Split' => $this->getSplit(),
                                            'PublicName' => $this->getPublicName(),
                                            'Category' => $this->getCategory(),
                                            'SubCategory' => $this->getSubCategory(),
                                            'SubSubCategory' => $this->getSubSubCategory(),
                                            'PhysicalAddress1' => $this->getPhysicalAddress1(),
                                            'PhysicalCity' => $this->getPhysicalCity(),
                                            'PhysicalCounty'=> $this->getPhysicalCounty(),
                                            'WebsiteAddress'=> $this->getWebsiteAddress(),
                                            'AgencyDescription' => $this->getAgencyDescription(),
                                            'Eligibility'=> $this->getEligibility(),
                                            'TaxonomyTerms'=> $this->getTaxonomyTerms(),

                                            'ParentAgency'=> $this->ParentAgency,
                                            'PublicName_Program'=> $this->PublicName_Program,
                                            'HoursOfOperation'=> $this->HoursOfOperation,
                                            'LanguagesOffered'=> $this->LanguagesOffered,
                                            'LanguagesOfferedList'=> $this->LanguagesOfferedList,
                                            'ApplicationProcess'=> $this->ApplicationProcess,
                                            'Coverage'=> $this->Coverage,
                                            'CoverageAreaText'=> $this->CoverageAreaText,
                                            'PhysicalAddress2'=> $this->PhysicalAddress2(),
                                            'PhysicalStateProvince'=> $this->PhysicalStateProvince,
                                            'PhysicalPostalCode'=> $this->PhysicalPostalCode,
                                            'MailingAttentionName'=> $this->MailingAttentionName,
                                            'MailingAddress1'=> $this->MailingAddress1,
                                            'MailingAddress2'=> $this->MailingAddress2,
                                            'MailingCity'=> $this->MailingCity,
                                            'MailingStateProvince'=> $this->MailingStateProvince,
                                            'MailingPostalCode'=> $this->MailingPostalCode,
                                            'DisabilitiesAccess'=> $this->DisabilitiesAccess,
                                            'Phone1Name'=> $this->Phone1Name,
                                            'Phone1Number'=> $this->Phone1Number,
                                            'Phone1Description'=> $this->Phone1Description,
                                            'PhoneNumberBusinessLine'=> $this->PhoneNumberBusinessLine,
                                            'PhoneTollFree'=> $this->PhoneTollFree,
                                            'PhoneFax'=> $this->PhoneFax,
                                            'EmailAddressMain'=> $this->EmailAddressMain,
                                            'Custom_Facebook'=> $this->Custom_Facebook,
                                            'Custom_Instagram'=> $this->Custom_Instagram,
                                            'Custom_LinkedIn'=> $this->Custom_LinkedIn,
                                            'Custom_Twitter'=> $this->Custom_Twitter,
                                            'Custom_YouTube'=> $this->Custom_YouTube,
                                            'Categories'=> $this->Categories,
                                            'LastVerifiedOn'=> $this->LastVerifiedOn,
                                            ),true);
                if($status){
                    DBFunctions::commit();
                    return true;
                }
            }
            return true; 
    }
    
    
    function update(){
        return false;
    }
    
    function delete(){
        return false;
    }
    
    function exists(){
        return true;
    }
    
    function getCacheId(){
        global $wgSitename;
    }
    
    function getId(){
        return $this->id;
    }

    function getResourceAgencyNum(){
        return $this->ResourceAgencyNum;
    }

    function getSplit(){
        return $this->Split;
    }
    function getPublicName(){
        return $this->PublicName;
    }
    function getCategory(){
        return $this->Category;
    }
    function getSubCategory(){
        return $this->SubCategory;
    }
    function getSubSubCategory(){
        return $this->SubSubCategory;
    }
    function getPhysicalAddress1(){
        return $this->PhysicalAddress1;
    }
    function getPhysicalCity(){
        return $this->PhysicalCity;
    }
    function getPhysicalCounty(){
        return $this->PhysicalCounty;
    }
    function getWebsiteAddress(){
        return $this->WebsiteAddress;
    }
    function getAgencyDescription(){
        return $this->AgencyDescription;
    }
    function getEligibility(){
        return $this->Eligibility;
    }
    function getTaxonomyTerms(){
        return $this->TaxonomyTerms;
    }    
}

?>
