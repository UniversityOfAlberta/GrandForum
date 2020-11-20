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

    }
    
    function doPUT(){

    }
    
    function doDELETE(){

    }
	
}

?>
