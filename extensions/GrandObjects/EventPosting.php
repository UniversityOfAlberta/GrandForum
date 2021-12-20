<?php

/**
 * @package GrandObjects
 */

class EventPosting extends Posting {
    
    static $dbTable = 'grand_event_postings';

    var $address;
    var $city;
    var $province;
    var $country;
    var $website;
    var $image1;
    var $image2;
    var $image3;
    var $banner1;
    var $banner2;
    var $enableRegistration;
    var $enableMaterials;
    
    function __construct($data){
        if(count($data) > 0){
            $row = $data[0];
            parent::__construct($data);
            $this->address = $row['address'];
            $this->city = $row['city'];
            $this->province = $row['province'];
            $this->country = $row['country'];
            $this->website = $row['website'];
            $this->enableRegistration = $row['enable_registration'];
            $this->enableMaterials = $row['enable_materials'];
        }
    }
    
    function getAddress(){
        return $this->address;
    }
    
    function getCity(){
        return $this->city;
    }
    
    function getProvince(){
        return $this->province;
    }
    
    function getCountry(){
        return $this->country;
    }
    
    function getWebsite(){
        return $this->website;
    }
    
    function isRegistrationEnabled(){
        return ($this->enableRegistration == 1);
    }
    
    function isMaterialSubmissionEnabled(){
        return ($this->enableMaterials == 1);
    }
    
    function toArray(){
        $json = parent::toArray();
        $json['address'] = $this->getAddress();
        $json['city'] = $this->getCity();
        $json['province'] = $this->getProvince();
        $json['country'] = $this->getCountry();
        $json['website'] = $this->getWebsite();
        $json['image1'] = $this->getImageUrl(1);
        $json['image2'] = $this->getImageUrl(2);
        $json['image3'] = $this->getImageUrl(3);
        $json['banner1'] = $this->getImageUrl(4);
        $json['banner2'] = $this->getImageUrl(5);
        $json['enableRegistration'] = $this->isRegistrationEnabled();
        $json['enableMaterials'] = $this->isMaterialSubmissionEnabled();
        return $json;
    }
    
    function create(){
        $status = parent::create();
        if($status){
            $status = DBFunctions::update(self::$dbTable,
                                          array('address' => $this->address,
                                                'city' => $this->city,
                                                'province' => $this->province,
                                                'country' => $this->country,
                                                'website' => $this->website,
                                                'enable_registration' => $this->enableRegistration,
                                                'enable_materials' => $this->enableMaterials),
                                          array('id' => $this->id));
            $this->saveImage(1, $this->image1);
            $this->saveImage(2, $this->image2);
            $this->saveImage(3, $this->image3);
            $this->saveImage(4, $this->banner1);
            $this->saveImage(5, $this->banner2);
        }
        return $status;
    }
    
    function update(){
        $status = parent::update();
        if($status){
            $status = DBFunctions::update(self::$dbTable,
                                          array('address' => $this->address,
                                                'city' => $this->city,
                                                'province' => $this->province,
                                                'country' => $this->country,
                                                'website' => $this->website,
                                                'enable_registration' => $this->enableRegistration,
                                                'enable_materials' => $this->enableMaterials),
                                          array('id' => $this->id));
            $this->saveImage(1, $this->image1);
            $this->saveImage(2, $this->image2);
            $this->saveImage(3, $this->image3);
            $this->saveImage(4, $this->banner1);
            $this->saveImage(5, $this->banner2);
        }
        return $status;
    }
}

?>
