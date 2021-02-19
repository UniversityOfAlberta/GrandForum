<?php

class CRMOpportunityAPI extends RESTAPI {
    
    function doGET(){
        if($this->getParam('id') != ""){
            $opportunity = CRMOpportunity::newFromId($this->getParam('id'));
            return $opportunity->toJSON();
        }
        else{
            $contact = CRMContact::newFromId($this->getParam('contact_id'));
            $opportunities = new Collection($contact->getOpportunities());
            return $opportunities->toJSON();
        }
    }
    
    function doPOST(){
        $me = Person::newFromWgUser();
        if(CRMOpportunity::isAllowedToCreate()){
            $opportunity = new CRMOpportunity(array());
            $opportunity->contact = $this->POST('contact');
            $opportunity->owner = $this->POST('owner')->id;
            $opportunity->category = $this->POST('category');
            $opportunity->description = $this->POST('description');
            $opportunity->create();
            return $opportunity->toJSON();
        }
        else{
            $this->throwError("You are not allowed to create this Opportunity");
        }
    }
    
    function doPUT(){
        $opportunity = CRMOpportunity::newFromId($this->getParam('id'));
        if($opportunity->isAllowedToEdit()){
            $opportunity->owner = $this->POST('owner')->id;
            $opportunity->category = $this->POST('category');
            $opportunity->description = $this->POST('description');
            $opportunity->update();
            return $opportunity->toJSON();
        }
        else{
            $this->throwError("You are not allowed to edit this Opportunity");
        }
    }
    
    function doDELETE(){
        $opportunity = CRMOpportunity::newFromId($this->getParam('id'));
        if($opportunity->isAllowedToEdit()){
            $opportunity->delete();
            return $opportunity->toJSON();
        }
        else{
            $this->throwError("You are not allowed to delete this Opportunity");
        }
    }
	
}

?>
