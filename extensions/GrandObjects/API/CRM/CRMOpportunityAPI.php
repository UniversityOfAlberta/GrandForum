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
            $opportunity->category = $this->POST('category');
            $opportunity->description = $this->POST('description');
            $opportunity->update();
            return $opportunity->toJSON();
        }
        else{
            $this->throwError("You are not allowed to edit this Contact");
        }
    }
    
    function doDELETE(){

    }
	
}

?>
