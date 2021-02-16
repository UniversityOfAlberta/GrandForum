<?php

class CRMContactAPI extends RESTAPI {
    
    function doGET(){
        if($this->getParam('id') != ""){
            $contact = CRMContact::newFromId($this->getParam('id'));
            return $contact->toJSON();
        }
        else{
            $contacts = new Collection(CRMContact::getAllContacts());
            return $contacts->toJSON();
        }
    }
    
    function doPOST(){
        $me = Person::newFromWgUser();
        if(CRMContact::isAllowedToCreate()){
            $contact = new CRMContact(array());
            $contact->title = $this->POST('title');
            $contact->owner = $me->getId();
            $contact->details = $this->POST('details');
            $contact->create();
            return $contact->toJSON();
        }
        else{
            $this->throwError("You are not allowed to create this Contact");
        }
    }
    
    function doPUT(){
        $contact = CRMContact::newFromId($this->getParam('id'));
        if($contact->isAllowedToEdit()){
            $contact->title = $this->POST('title');
            $contact->details = $this->POST('details');
            $contact->update();
            return $contact->toJSON();
        }
        else{
            $this->throwError("You are not allowed to edit this Contact");
        }
    }
    
    function doDELETE(){
        $contact = CRMContact::newFromId($this->getParam('id'));
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
