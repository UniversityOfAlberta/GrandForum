<?php

/**
 * @package GrandObjects
 */

class Province {
    
    static $cache = array();
    
    var $id;
    var $name;
    var $color;
    
    static function newFromId($id){
        if(isset($cache[$id])){
            return $cache[$id];
        }
        $data = DBFunctions::select(array('grand_provinces'),
                                    array('*'),
                                    array('id' => EQ($id)));
        $prov = new Province($data);
        $cache[$id] = $prov;
        return $prov;
    }
    
    static function newFromName($name){
      /*  if(isset($cache[$name])){
            return $cache[$name];
        }*/
        $data = DBFunctions::select(array('grand_provinces'),
                                    array('*'),
                                    array('province' => EQ($name)));
        $prov = new Province($data);
    //    $cache[$name] = $prov;
        return $prov;
    }
    
    function __construct($data){
        if(count($data) > 0){
            $row = $data[0];
            $this->id = $row['id'];
            $this->name = $row['province'];
            $this->color = $row['color'];
        }
    }
    
    function getId(){
        return $this->id;
    }
    
    function getName(){
        return $this->name;
    }
    
    function getColor(){
        return $this->color;
    }
    
}

?>
