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
    var $order = 1000;
    var $extras;
    var $isDefault = 0;
    var $provinceString;
    
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
    
    static function getAllUniversities(){
        $data = DBFunctions::select(array('grand_universities'),
                                    array('university_id', '`order`'),
                                    array(),
                                    array('`order`' => 'ASC',
                                          'university_name' => 'ASC'));
        $unis = array();
        foreach($data as $row){
            $unis[] = University::newFromId($row['university_id']);
        }
        return $unis;
    }

    static function getNearestUniversity($lat, $long){
	$sql = "SELECT * , SQRT( POW( ABS( latitude - ($lat)) , 2 ) + POW( ABS( longitude -($long)) , 2 ) ) AS dist
FROM `grand_universities` WHERE `university_name` <> 'Unknown' AND `longitude` <> 'NULL' AND `latitude` <> 'NULL'
ORDER BY `dist` ASC LIMIT 10";
	$data = DBFunctions::execSQL($sql);
	$unis = array();
	foreach($data as $row){
	    $unis[] = University::newFromId($row['university_id']);
	}
        return $unis;

    }
    
    function __construct($data){
        if(count($data) > 0){
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
            $this->extras = $row['extras'];
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
                      'default' => $this->isDefault(),
		              'shortName' => $this->getShortName(),
                      'phone'=> $this->getPhone(),
                     'hours'=> $this->getHours());
        return $json;
    }
    
    function create(){
            $me = Person::newFromWgUser();
            if($me->isRoleAtLeast(EXTERNAL)){
                DBFunctions::begin();
                $status = DBFunctions::insert('grand_universities',
                                              array('university_name' => $this->getName(),
						                            'short_name' => $this->getShortName(),
                                                    'province_id' => $this->getProvince(),
                                                    'latitude' => $this->getLatitude(),
						                            'longitude' => $this->getLongitude(),
						                            '`order`' => $this->getOrder(),
                                                    'extras' => $this->extras),true);
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
        if($this->province == ""){
            $this->province = $this->findProvince($this->provinceString);
        }
        return $this->province;
    }
    
    function getColor(){
        return $this->color;
    }
    
    function getOrder(){
        return $this->order;
    }

    function getExtras(){
        if(isset($this->extras)){
            return unserialize($this->extras);
        }
        return "";
    }

    function getPhone(){
        $extras = $this->getExtras();
        if($extras != ""){
            return $extras['phone'];
        }
        return "";
        
    }

    function getHours(){
        $extras = $this->getExtras();
        $hours = "";
        if($extras != "" && $extras['timeFrom'] != "" && $extras['timeTo'] != ""){
            $hours = "{$extras['timeFrom']} - {$extras['timeTo']}";
        }
        return $hours;
    }
    
    function isDefault(){
        return $this->isDefault;
    }

    function setId($var){
        $this->id = $var;
    }

    function setName($var){
        $this->name = $var;
    }

    function setShortName($var){
        $this->shortName = $var;
    }

    function setLatitude($var){
        $this->latitude = $var;
    }

    function setLongitude($var){
        $this->longitude = $var;
    }

    function setProvinceString($var){
       $this->provinceString = $var;
    }

    function setProvince($var){
       $this->province = $var;
    }

    function setColor($var){
        $this->color = $var;
    }

    function setOrder($var){
        $this->order = $var;
    }

    function setDefault($var){
        $this->isDefault = $var;
    }

    function setExtras($var){
        $this->extras = serialize($var);
    }


    private function findProvince($prov){
        $sql = "SELECT *
                FROM grand_provinces
                WHERE province LIKE '$prov'";
        $data = DBFunctions::execSQL($sql);
	if(count($data)>0){
	    return $data[0]['id'];
	}
	$status = DBFunctions::insert('grand_provinces',
                                     array('province' => $prov),true);
	if($status){
            $sql = "SELECT *
                    FROM grand_provinces
                    WHERE province LIKE '$prov'";
            $data = DBFunctions::execSQL($sql);
            if(count($data)>0){
                return $data[0]['id'];
            }
	}
	return 1;
    }
    
}

?>
