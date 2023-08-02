<?php

class LIMSContactAPI extends RESTAPI {
    
    function doGET(){
        if($this->getParam('id') != ""){
            $contact = LIMSContact::newFromId($this->getParam('id'));
            return $contact->toJSON();
        }
        else{
            $contacts = new Collection(LIMSContact::getAllContacts());
            return $contacts->toJSON();
        }
    }
    
    function doPOST(){
        $me = Person::newFromWgUser();
        if(LIMSContact::isAllowedToCreate()){
            $contact = new LIMSContact(array());
            $contact->title = $this->POST('title');
            $contact->owner = $me->getId();
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
        $contact = LIMSContact::newFromId($this->getParam('id'));
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
        $contact = LIMSContact::newFromId($this->getParam('id'));
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
