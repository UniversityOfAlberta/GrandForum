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
    
    function EventPosting($data){
        if(count($data) > 0){
            $row = $data[0];
            parent::posting($data);
            $this->address = $row['address'];
            $this->city = $row['city'];
            $this->province = $row['province'];
            $this->country = $row['country'];
            $this->website = $row['website'];
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
                                                'website' => $this->website),
                                          array('id' => $this->id));
            $this->saveImage(1, $this->image1);
            $this->saveImage(2, $this->image2);
            $this->saveImage(3, $this->image3);
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
                                                'website' => $this->website),
                                          array('id' => $this->id));
            $this->saveImage(1, $this->image1);
            $this->saveImage(2, $this->image2);
            $this->saveImage(3, $this->image3);
        }
        return $status;
    }
}

?>
