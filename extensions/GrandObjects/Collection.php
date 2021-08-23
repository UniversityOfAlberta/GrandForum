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
    function Collection(&$objects){
        $this->objects = (is_array($objects)) ? $objects : array();
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
     * Returns a new Collection starting at index $start and contain at most $count entries
     */
    function paginate($start, $count){
        $array = array();
        foreach($this->objects as $i => $object){
            if($i >= $start && count($array) < $count){
                $array[] = $object;
            }
        }
        return new Collection($array);
    }
    
    /**
     * @return mixed Returns an array version of this Collection
     */
    function toArray(){
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
        return $json;
    }
    
    /**
     * @return mixed Returns a jsonified version of this Collection
     */
    function toJSON(){
        return json_encode($this->toArray());
    }
    
    function toSimpleArray(){
        $json = array();
        if(count($this->objects) > 0){
            $objs = array_values($this->objects);
            if($objs[0] instanceof BackboneModel){
                foreach($this->objects as $object){
                    $json[] = $object->toSimpleArray();
                }
            }
            else{
                foreach($this->objects as $object){
                    $json[] = $object;
                }
            }
        }
        return $json;
    }
    
    /**
     * @return mixed Returns a jsonified version of this Collection
     */
    function toSimpleJSON(){
        return json_encode($this->toSimpleArray());
    }

}

?>
