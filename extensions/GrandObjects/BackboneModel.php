<?php

/**
 * Used to provide RESTful functionality to objects
 * @package GrandObjects
 */
abstract class BackboneModel {

    var $id;

    /**
     * @return int Returns the id of this BackboneModel
     */
    function getId(){
        return $this->id;
    }

    /**
     * Generates and returns a jsonified version of this BackboneModel
     * @return mixed Returns a jsonified version of this BackboneModel
     */
    function toJSON(){
       
        return json_encode($this->toArray());
    }
    
    /**
     * Generates and returns an array version of this BackboneModel
     * @return array Returns an array version of this BackboneModel
     */
    abstract function toArray();
    
    /**
     * Creates a new record in the DB for this BackboneModel
     * @return boolean Returns whether or not the creation was successful or not
     */
    abstract function create();
    
    /**
     * Updates the record in the DB for this BackboneModel
     * @return boolean Returns whether or not the update was successful or not
     */
    abstract function update();
    
    /**
     * Deletes the record in the DB for this BackboneModel
     * @return boolean Returns whether or not the deletion was successful or not
     */
    abstract function delete();
    
    /**
     * Returns whether or not this BackboneModel exists or not
     * @return boolean Returns whether or not this BackboneModel exists or not
     */
    abstract function exists();
    
    /**
     * Returns the id of this BackboneModel's cache
     * @return string Returns the id of this BackboneModel's cache
     */
    abstract function getCacheId();
}

?>
