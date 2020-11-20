<?php

/**
 * @package GrandObjects
 */

class CRMContact extends BackboneModel {

    static $cache = array();

    var $id;
    var $title;
    var $owner;
    var $details;
	
	static function newFromId($id){
	    if(!isset(self::$cache[$id])){
	        $data = DBFunctions::select(array('grand_crm_contact'),
	                                    array('*'),
	                                    array('id' => $id));
	        self::$cache[$id] = new CRMContact($data);
	    }
	    return self::$cache[$id];
	}
	
	static function getAllContacts(){
	    $data = DBFunctions::select(array('grand_crm_contact'),
	                                array('id'),
	                                array());
	    $contacts = array();
	    foreach($data as $row){
	        $contacts[] = CRMContact::newFromId($row['id']);
	    }
	    return $contacts;
	}
	
	function CRMContact($data){
	    if(count($data) > 0){
		    $this->id = $data[0]['id'];
		    $this->title = $data[0]['title'];
		    $this->owner = $data[0]['owner'];
		    $this->details = $data[0]['details'];
		}
	}
	
	function getId(){
	    return $this->id;
	}
	
	function getTitle(){
	    return $this->title;
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
	
	function getUrl(){
	    global $wgServer, $wgScriptPath;
	    return "{$wgServer}{$wgScriptPath}/index.php/Special:CRM#/{$this->getId()}";
	}
	
	function toArray(){
	    $person = $this->getPerson();
	    $owner = array('id' => $person->getId(),
	                   'name' => $person->getNameForForms(),
	                   'url' => $person->getUrl());
	    
	    $json = array('id' => $this->getId(),
	                  'title' => $this->getTitle(),
	                  'owner' => $owner,
	                  'details' => $this->getDetails(),
	                  'url' => $this->getUrl());
	    return $json;
	}
	
	function create(){
	    $me = Person::newFromWgUser();
	    $this->owner = $me->getId();
	    DBFunctions::insert('grand_crm_contact',
	                        array('title' => $this->title,
	                              'owner' => $this->owner,
	                              'details' => $this->details));
	    $this->id = DBFunctions::insertId();
	}
	
	function update(){
	    $me = Person::newFromWgUser();
	    $this->owner = $me->getId();
	    DBFunctions::update('grand_crm_contact',
	                        array('title' => $this->title,
	                              'owner' => $this->owner,
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
