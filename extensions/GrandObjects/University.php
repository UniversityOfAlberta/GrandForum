<?php

/**
 * @package GrandObjects
 */

class University extends BackboneModel {
    
    static $cache = array();
    
    var $id;
    var $name;
    var $shortName;
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
        if(empty($data)){
            $data = DBFunctions::select(array('grand_universities' => 'u', 
                                              'grand_provinces' => 'p'),
                                        array('u.*', 'p.province', 'p.color' => 'col'),
                                        array('university_id' => EQ($id),
                                              'u.province_id' => EQ(0),
                                              'province' => EQ('Other')));
        }
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
        if(empty($data)){
            $data = DBFunctions::select(array('grand_universities' => 'u', 
                                              'grand_provinces' => 'p'),
                                        array('u.*', 'p.province', 'p.color' => 'col'),
                                        array('university_name' => EQ($name),
                                              'u.province_id' => EQ(0),
                                              'province' => EQ('Other')));
        }
        $university = new University($data);
        $cache[$name] = $university;
        return $university;
    }
    
    static function getAllUniversities(){
        $data = DBFunctions::select(array('grand_universities'),
                                    array('university_id', '`order`'),
                                    array(),
                                    array('`order`' => 'ASC',
                                          'university_name' => 'ASC'));
        $unis = array();
        foreach($data as $row){
            $uni = University::newFromId($row['university_id']);
            if($uni->getId() != null){
                $unis[] = $uni;
            }
        }
        return $unis;
    }
    
    function __construct($data){
        if(!empty($data)){
            $row = $data[0];
            $this->id = $row['university_id'];
            $this->name = $row['university_name'];
            $this->shortName = $row['short_name'];
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
        $json = array('id' => $this->getId(),
                      'name' => $this->getName(),
                      'latitude' => $this->getLatitude(),
                      'longitude' => $this->getLongitude(),
                      'color' => $this->getColor(),
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
    
    function getShortName(){
        if($this->shortName == ""){
            return $this->getName();
        }
        return $this->shortName;
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
