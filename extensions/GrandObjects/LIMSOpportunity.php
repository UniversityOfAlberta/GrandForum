<?php

/**
 * @package GrandObjects
 */

class LIMSOpportunity extends BackboneModel {

    var $id;
    var $contact;
    var $owner;
    var $userType;
    var $description;
    var $category;
    var $date;
	
	static function newFromId($id){
	    $data = DBFunctions::select(array('grand_lims_opportunity'),
	                                array('*'),
	                                array('id' => $id));
	    $opportunity = new LIMSOpportunity($data);
	    return $opportunity;
	}
	
	static function getOpportunities($contact_id){
	    $data = DBFunctions::select(array('grand_lims_opportunity'),
	                                array('*'),
	                                array('contact' => $contact_id));
	    $opportunities = array();
	    foreach($data as $row){
	        $opportunity = new LIMSOpportunity(array($row));
	        if($opportunity->isAllowedToView()){
	            $opportunities[] = $opportunity;
	        }
	    }
	    return $opportunities;
	}
	
	function LIMSOpportunity($data){
	    if(count($data) > 0){
		    $this->id = $data[0]['id'];
		    $this->contact = $data[0]['contact'];
		    $this->owner = $data[0]['owner'];
		    $this->userType = $data[0]['user_type'];
		    $this->description = $data[0]['description'];
		    $this->category = $data[0]['category'];
		    $this->date = $data[0]['date'];
		}
	}
	
	function getId(){
	    return $this->id;
	}
	
	function getContact(){
	    return LIMSContact::newFromId($this->contact);
	}
	
	function getPerson(){
	    return Person::newFromId($this->owner);
	}
	
	function getOwner(){
	    return $this->owner;
	}
	
	function getUserType(){
	    return $this->userType;
	}
	
	function getDescription(){
	    return $this->description;
	}
	
	function getCategory(){
	    return $this->category;
	}
	
	function getDate(){
	    return $this->date;
	}
	
	function getTasks(){
	    return LIMSTask::getTasks($this->getId());
	}
	
	function isAllowedToEdit(){
        return ($this->getContact()->isAllowedToEdit() || $this->getPerson()->isMe());
    }
    
    function isAllowedToView(){
        return $this->getContact()->isAllowedToView();
    }
    
    static function isAllowedToCreate(){
        return LIMSContact::isAllowedToCreate();
    }
	
	function toArray(){
	    if($this->isAllowedToView()){
	        $person = $this->getPerson();
	        $owner = array('id' => $person->getId(),
	                       'name' => $person->getNameForForms(),
	                       'url' => $person->getUrl());
	        $json = array('id' => $this->getId(),
	                      'contact' => $this->getContact()->getId(),
	                      'owner' => $owner,
	                      'userType' => $this->getUserType(),
	                      'description' => $this->getDescription(),
	                      'category' => $this->getCategory(),
	                      'date' => $this->getDate(),
	                      'isAllowedToEdit' => $this->isAllowedToEdit());
	        return $json;
	    }
	    return array();
	}
	
	function create(){
	    if(self::isAllowedToCreate()){
	        DBFunctions::insert('grand_lims_opportunity',
	                            array('contact' => $this->contact,
	                                  'owner' => $this->owner,
	                                  'user_type' => $this->userType,
	                                  'description' => $this->description,
	                                  'category' => $this->category,
	                                  'date' => COL('CURRENT_TIMESTAMP')));
	        $this->id = DBFunctions::insertId();
	    }
	}
	
	function update(){
	    if($this->isAllowedToEdit()){
	        DBFunctions::update('grand_lims_opportunity',
	                            array('contact' => $this->contact,
	                                  'owner' => $this->owner,
	                                  'user_type' => $this->userType,
	                                  'description' => $this->description,
	                                  'category' => $this->category),
	                            array('id' => $this->id));
	    }
	}
	
	function delete(){
	    if($this->isAllowedToEdit()){
	        DBFunctions::delete('grand_lims_opportunity',
	                            array('id' => $this->id));
	        DBFunctions::delete('grand_lims_task',
	                            array('opportunity' => $this->id));
	        $this->id = "";
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
