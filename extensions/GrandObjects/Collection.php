<?php

/**
 * This class provides some helpful functions for querying objects in an array
 * @package GrandObjects
 */
class Collection {

    var $objects;

    /**
     * @param array $objects The array of objects to populate this Collection
     */
    function __construct($objects){
        $this->objects = $objects;
    }

    /**
     * Plucks an attribute out of the objects array and returns the array full of those attributes
     * @param array $attribute the attribute to pluck
     * @return array Returns an array full of the plucked attributes
     */
    function pluck($attribute){
        $array = array();
        foreach($this->objects as $object){
            if(is_array($object)){
                $array[] = $object[$attribute];
            }
            else{
                eval('$array[] = $object->'.$attribute.';');
            }
        }
        return $array;
    }
    
    /**
     * @return mixed Returns a jsonified version of this Collection
     */
    function toJSON(){
        $json = array();
        if(count($this->objects) > 0){
            if($this->objects[0] instanceof BackboneModel){
                foreach($this->objects as $object){
                    $json[] = $object->toArray();
                }
            }
            else{
                foreach($this->objects as $object){
                    $json[] = $object;
                }
            }
        }
        return json_encode($json);
    }

}

?>
