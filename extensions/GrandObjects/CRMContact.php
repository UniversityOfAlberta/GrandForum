<?php

/**
 * @package GrandObjects
 */

class CRMContact extends BackboneModel {

    static $cache = array();

    var $id;
    var $title;
    var $owner;
    var $details = array();
	
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
	        $contact = CRMContact::newFromId($row['id']);
	        if($contact->isAllowedToView()){
	            $contacts[] = $contact;
	        }
	    }
	    return $contacts;
	}
	
	function CRMContact($data){
	    if(count($data) > 0){
		    $this->id = $data[0]['id'];
		    $this->title = $data[0]['title'];
		    $this->owner = $data[0]['owner'];
		    $this->details = json_decode($data[0]['details']);
		    if($this->details == null){
		        $this->details = array();
		    }
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
	    return $this->owner;
	}
	
	function getDetails(){
	    return $this->details;
	}
	
	function getUrl(){
	    global $wgServer, $wgScriptPath;
	    return "{$wgServer}{$wgScriptPath}/index.php/Special:CRM#/{$this->getId()}";
	}
	
	function getOpportunities(){
	    return CRMOpportunity::getOpportunities($this->getId());
	}
	
	function isAllowedToEdit(){
        $me = Person::newFromWgUser();
        return ($me->getId() == $this->getOwner() || $me->isRoleAtLeast(STAFF));
    }
    
    function isAllowedToView(){
        $me = Person::newFromWgUser();
        return $me->isLoggedIn();
    }
    
    static function isAllowedToCreate(){
        $me = Person::newFromWgUser();
        return $me->isLoggedIn();
    }
	
	function toArray(){
	    if($this->isAllowedToView()){
	        $person = $this->getPerson();
	        $owner = array('id' => $person->getId(),
	                       'name' => $person->getNameForForms(),
	                       'url' => $person->getUrl());
	        $opportunities = array();
	        foreach($this->getOpportunities() as $opportunity){
	            $opp = $opportunity->toArray();
	            $tasks = array();
	            foreach($opportunity->getTasks() as $task){
	                $tasks[] = $task->toArray();
	            }
	            $opp['tasks'] = $tasks;
	            $opportunities[] = $opp;
	        }
	        
	        $json = array('id' => $this->getId(),
	                      'title' => $this->getTitle(),
	                      'owner' => $owner,
	                      'details' => $this->getDetails(),
	                      'url' => $this->getUrl(),
	                      'isAllowedToEdit' => $this->isAllowedToEdit(),
	                      'opportunities' => $opportunities);
	        return $json;
	    }
	    return null;
	}
	
	function create(){
	    if(self::isAllowedToCreate()){
	        $me = Person::newFromWgUser();
	        $this->owner = $me->getId();
	        DBFunctions::insert('grand_crm_contact',
	                            array('title' => $this->title,
	                                  'owner' => $this->owner,
	                                  'details' => json_encode($this->details)));
	        $this->id = DBFunctions::insertId();
	    }
	}
	
	function update(){
	    if($this->isAllowedToEdit()){
	        $me = Person::newFromWgUser();
	        $this->owner = $me->getId();
	        DBFunctions::update('grand_crm_contact',
	                            array('title' => $this->title,
	                                  'owner' => $this->owner,
	                                  'details' => json_encode($this->details)),
	                            array('id' => $this->id));
	    }
	}
	
	function delete(){
	    if($this->isAllowedToEdit()){
	    
	    }
	}
	
	function exists(){
        return ($this->getId() > 0);
	}
	
	function getCacheId(){
	    global $wgSitename;
	}
}
?>
