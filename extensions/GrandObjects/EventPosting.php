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
    
    function EventPosting($data){
        if(count($data) > 0){
            $row = $data[0];
            parent::posting($data);
            $this->address = $row['address'];
            $this->city = $row['city'];
            $this->province = $row['province'];
            $this->country = $row['country'];
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
    
    function toArray(){
        $json = parent::toArray();
        $json['address'] = $this->getAddress();
        $json['city'] = $this->getCity();
        $json['province'] = $this->getProvince();
        $json['country'] = $this->getCountry();
        return $json;
    }
    
    function create(){
        $status = parent::create();
        if($status){
            $status = DBFunctions::update(self::$dbTable,
                                          array('address' => $this->address,
                                                'city' => $this->city,
                                                'province' => $this->province,
                                                'country' => $this->country),
                                          array('id' => $this->id));
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
                                                'country' => $this->country),
                                          array('id' => $this->id));
        }
        return $status;
    }
}

?>
