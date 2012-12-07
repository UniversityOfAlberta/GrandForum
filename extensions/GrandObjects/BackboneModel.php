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
     * Generates and returns a jsonified version of this object
     * @return mixed Returns a jsonified version of this object
     */
    abstract function toJSON();
    
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
     * Deleted the record in the DB for this BackboneModel
     * @return boolean Returns whether or not the deletion was successful or not
     */
    abstract function delete();
}

?>
