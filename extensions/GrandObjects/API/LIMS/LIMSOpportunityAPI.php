<?php

class LIMSOpportunityAPI extends RESTAPI {
    
    function doGET(){
        if($this->getParam('id') != ""){
            $opportunity = LIMSOpportunity::newFromId($this->getParam('id'));
            return $opportunity->toJSON();
        }
        else{
            $contact = LIMSContact::newFromId($this->getParam('contact_id'));
            $opportunities = new Collection($contact->getOpportunities());
            return $opportunities->toJSON();
        }
    }
    
    function doPOST(){
        $me = Person::newFromWgUser();
        if(LIMSOpportunity::isAllowedToCreate()){
            $opportunity = new LIMSOpportunity(array());
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
        $opportunity = LIMSOpportunity::newFromId($this->getParam('id'));
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
        $opportunity = LIMSOpportunity::newFromId($this->getParam('id'));
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
