<?php

class CRMTaskAPI extends RESTAPI {
    
    function doGET(){
        if($this->getParam('id') != ""){
            $opportunity = CRMTask::newFromId($this->getParam('id'));
            return $opportunity->toJSON();
        }
        else{
            $opportunity = CRMOpportunity::newFromId($this->getParam('opportunity_id'));
            $tasks = new Collection($opportunity->getTasks());
            return $tasks->toJSON();
        }
    }
    
    function doPOST(){
        $me = Person::newFromWgUser();
        if(CRMOpportunity::isAllowedToCreate()){
            $opportunity = new CRMOpportunity();
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
