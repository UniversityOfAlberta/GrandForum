<?php

/**
 * @package GrandObjects
 */

class CRMOpportunity extends BackboneModel {

    var $id;
    var $contact;
    var $description;
    var $category;
	
	static function newFromId($id){
	    $data = DBFunctions::select(array('grand_crm_opportunity'),
	                                array('*'),
	                                array('id' => $id));
	    $opportunity = new CRMOpportunity($data);
	    return $opportunity;
	}
	
	static function getOpportunities($contact_id){
	    $data = DBFunctions::select(array('grand_crm_opportunity'),
	                                array('*'),
	                                array('contact' => $contact_id));
	    $opportunities = array();
	    foreach($data as $row){
	        $opportunity = new CRMOpportunity(array($row));
	        if($opportunity->isAllowedToView()){
	            $opportunities[] = $opportunity;
	        }
	    }
	    return $opportunities;
	}
	
	function CRMOpportunity($data){
	    if(count($data) > 0){
		    $this->id = $data[0]['id'];
		    $this->contact = $data[0]['contact'];
		    $this->description = $data[0]['description'];
		    $this->category = $data[0]['category'];
		}
	}
	
	function getId(){
	    return $this->id;
	}
	
	function getContact(){
	    return CRMContact::newFromId($this->contact);
	}
	
	function getDescription(){
	    return $this->description;
	}
	
	function getCategory(){
	    return $this->category;
	}
	
	function getTasks(){
	    return CRMTask::getTasks($this->getId());
	}
	
	function isAllowedToEdit(){
        return $this->getContact()->isAllowedToEdit();
    }
    
    function isAllowedToView(){
        return $this->getContact()->isAllowedToView();
    }
    
    static function isAllowedToCreate(){
        return CRMContact::isAllowedToCreate();
    }
	
	function toArray(){
	    if($this->isAllowedToView()){
	        $json = array('id' => $this->getId(),
	                      'contact' => $this->getContact()->getId(),
	                      'description' => $this->getDescription(),
	                      'category' => $this->getCategory());
	        return $json;
	    }
	    return array();
	}
	
	function create(){
	    DBFunctions::insert('grand_crm_opportunity',
	                        array('contact' => $this->contact,
	                              'description' => $this->description,
	                              'category' => $this->category));
	    $this->id = DBFunctions::insertId();
	}
	
	function update(){
	    DBFunctions::update('grand_crm_opportunity',
	                        array('contact' => $this->contact,
	                              'description' => $this->description,
	                              'category' => $this->category),
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
