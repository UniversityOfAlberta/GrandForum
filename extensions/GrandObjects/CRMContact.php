<?php

/**
 * @package GrandObjects
 */

class CRMContact extends BackboneModel {

    var $id;
    var $owner;
    var $details;
	
	static function newFromId($id){
	    $data = DBFunctions::select(array('grand_crm_contact'),
	                                array('*'),
	                                array('id' => $id));
	    $contact = new CRMContact($data);
	    return $contact;
	}
	
	function CRMContact($data){
	    if(count($data) > 0){
		    $this->id = $data[0]['id'];
		    $this->owner = $data[0]['owner'];
		    $this->details = $data[0]['details'];
		}
	}
	
	function getId(){
	    return $this->id;
	}
	
	function getPerson(){
	    return Person::newFromId($this->owner);
	}
	
	function getOwner(){
	    return Person::newFromId($this->owner);
	}
	
	function getDetails(){
	    return $this->details;
	}
	
	function toArray(){
	    $json = array('id' => $this->getId(),
	                  'owner' => $this->getOwner(),
	                  'details' => $this->getDetails());
	    return $json;
	}
	
	function create(){
	    DBFunctions::insert('grand_crm_contact',
	                        array('owner' => $this->owner,
	                              'details' => $this->details));
	    $this->id = DBFunctions::insertId();
	}
	
	function update(){
	    DBFunctions::update('grand_crm_contact',
	                        array('owner' => $this->owner,
	                              'details' => $this->details),
	                        array('id' => $this->id));
	}
	
	function delete(){
	    
	}
	
	function exists(){
        return ($this->getId() > 0);
	}
	
	function getCacheId(){
	    global $wgSitename;
	}
}
?>
