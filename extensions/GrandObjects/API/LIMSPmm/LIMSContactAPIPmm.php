<?php

class LIMSContactAPIPmm extends RESTAPI {
    
    function doGET(){
        if($this->getParam('id') != ""){
            $contact = LIMSContactPmm::newFromId($this->getParam('id'));
            return $contact->toJSON();
        }
        else{
            $contacts = new Collection(LIMSContactPmm::getAllContacts());
            return $contacts->toJSON();
        }
    }
    
    function doPOST(){
        $me = Person::newFromWgUser();
        if(LIMSContactPmm::isAllowedToCreate()){
            $contact = new LIMSContactPmm(array());
            $contact->title = $this->POST('title');
            $contact->owner = $me->getId();
            $contact->projectId = $this->POST('projectId');
            $contact->details = $this->POST('details');
            // Trim the details
            foreach($contact->details as $key => $value){
                $contact->details->{$key} = trim($value);
            }
            $contact->projects = $this->POST('projects');
            // Validate first
            $validation = $contact->validate();
            if($validation !== true){
                $this->throwError($validation);
            }
            // Now create
            $contact->create();
            return $contact->toJSON();
        }
        else{
            $this->throwError("You are not allowed to create this Contact");
        }
    }
    
    function doPUT(){
        $contact = LIMSContactPmm::newFromId($this->getParam('id'));
        if($contact->isAllowedToEdit()){
            $contact->title = $this->POST('title');
            $contact->details = $this->POST('details');
            // Trim the details
            foreach($contact->details as $key => $value){
                $contact->details->{$key} = trim($value);
            }
            $contact->projects = $this->POST('projects');
            // Validate first
            $validation = $contact->validate();
            if($validation !== true){
                $this->throwError($validation);
            }
            // Now update
            $contact->update();
            return $contact->toJSON();
        }
        else{
            $this->throwError("You are not allowed to edit this Contact");
        }
    }
    
    function doDELETE(){
        $contact = LIMSContactPmm::newFromId($this->getParam('id'));
        if($contact->isAllowedToEdit()){
            $contact->delete();
            return $contact->toJSON();
        }
        else{
            $this->throwError("You are not allowed to delete this Contact");
        }
    }
	
}

?>
