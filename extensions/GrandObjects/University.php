<?php

class University extends BackboneModel {
    
    static $cache = array();
    
    var $id;
    var $name;
    var $latitude;
    var $longitude;
    var $color;
    var $province;
    var $order;
    var $isDefault;
    
    static function newFromId($id){
        if(isset($cache[$id])){
            return $cache[$id];
        }
        $data = DBFunctions::select(array('grand_universities' => 'u', 
                                          'grand_provinces' => 'p'),
                                    array('u.*', 'p.province', 'p.color' => 'col'),
                                    array('university_id' => EQ($id),
                                          'province_id' => EQ(COL('p.id'))));
        $university = new University($data);
        $cache[$id] = $university;
        return $university;
    }
    
    static function newFromName($name){
        if(isset($cache[$name])){
            return $cache[$name];
        }
        $data = DBFunctions::select(array('grand_universities' => 'u', 
                                          'grand_provinces' => 'p'),
                                    array('u.*', 'p.province', 'p.color' => 'col'),
                                    array('university_name' => EQ($name),
                                          'province_id' => EQ(COL('p.id'))));
        $university = new University($data);
        $cache[$name] = $university;
        return $university;
    }
    
    function University($data){
        if(count($data) > 0){
            $row = $data[0];
            $this->id = $row['university_id'];
            $this->name = $row['university_name'];
            $this->latitude = $row['latitude'];
            $this->longitude = $row['longitude'];
            $this->province = $row['province'];
            $this->color = $row['col'];
            $this->order = $row['order'];
            $this->isDefault = $row['default'];
        }
    }
    
    function toArray(){
        global $wgUser;
        $privateProfile = "";
        $publicProfile = $this->getProfile(false);
        if($wgUser->isLoggedIn()){
            $privateProfile = $this->getProfile(true);
        }
        $json = array('id' => $this->getId(),
                      'name' => $this->getName(),
                      'latitude' => $this->getRealName(),
                      'longitude' => $this->getNameForForms(),
                      'color' => $this->getReversedName(),
                      'order' => $this->getOrder(),
                      'default' => $this->isDefault());
        return $json;
    }
    
    function create(){ 
        return false;
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
    
    function getName(){
        return $this->name;
    }
    
    function getLatitude(){
        return $this->latitude;
    }
    
    function getLongitude(){
        return $this->longitude;
    }
    
    function getProvince(){
        return $this->province;
    }
    
    function getColor(){
        return $this->color;
    }
    
    function getOrder(){
        return $this->order;
    }
    
    function isDefault(){
        return $this->isDefault;
    }
    
}

?>
