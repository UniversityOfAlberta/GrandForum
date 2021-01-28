<?php

/**
 * @package GrandObjects
 */

class Discipline {
    
    static $cache = array();
    
    var $id;
    var $name;
    var $color;
    
    /**
     * Returns a new Discipline from the given id
     * @param int $id The id of the Discipline
     * @return Discipline The Discipline with the given id
     */
    static function newFromId($id){
        if(!isset($cache[$id])){
            $data = DBFunctions::select(array('grand_disciplines'),
                                        array('*'),
                                        array('id' => EQ($id)));
            $disc = new Discipline($data);
            $cache[$id] = &$disc;
        }
        return $cache[$id];
    }
    
    /**
     * Returns a new Discipline from the given name
     * @param string $name The name of the Discipline
     * @return Discipline The Discipline with the given name
     */
    static function newFromName($name){
        if(!isset($cache[$name])){
            $data = DBFunctions::select(array('grand_disciplines'),
                                        array('*'),
                                        array('discipline' => EQ($name)));
            $disc = new Discipline($data);
            $cache[$name] = &$disc;
        }
        return $cache[$name];
    }
    
    function __construct($data){
        if(count($data) > 0){
            $row = $data[0];
            $this->id = $row['id'];
            $this->name = $row['discipline'];
            $this->color = $row['color'];
        }
    }
    
    /**
     * Returns this Discipline's id
     * @return int This Discipline's id
     */
    function getId(){
        return $this->id;
    }
    
    /**
     * Returns this Discipline's name
     * @return string This Discipline's name
     */
    function getName(){
        return $this->name;
    }
    
    /**
     * Returns this Discipline's color
     * @return string This Discipline's color
     */
    function getColor(){
        return $this->color;
    }
    
}

?>
