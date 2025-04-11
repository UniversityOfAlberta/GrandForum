<?php

class LIMSOpportunityAPI extends RESTAPI {
    
    function doGET(){
        $files = ($this->getParam('files') != "");
        $file_id = $this->getParam('file_id');
        if($this->getParam('id') != ""){
            $opportunity = LIMSOpportunity::newFromId($this->getParam('id'));
            if($files && $file_id != ""){
                $file = $opportunity->getFile($file_id);
                if(isset($file['data']) && isset($file['type']) && isset($file['filename'])){
                    header('Content-Type: '.$file['type']);
                    header('Content-Disposition: attachment; filename="'.$file['filename'].'"');
                    $exploded = explode("base64,", $file['data']);
                    echo base64_decode(@$exploded[1]);
                    exit;
                }
            }
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
            $opportunity->project = $this->POST('project')->id;
            $opportunity->userType = $this->POST('userType');
            $opportunity->category = $this->POST('category');
            $opportunity->surveyed = $this->POST('surveyed');
            $opportunity->responded = $this->POST('responded');
            $opportunity->satisfaction = $this->POST('satisfaction');
            $opportunity->status = $this->POST('status');
            $opportunity->description = $this->POST('description');
            $opportunity->products = $this->POST('products');
            $opportunity->files = $this->POST('files');
            $opportunity->create();
            return $opportunity->toJSON();
        }
        else{
            $this->throwError("You are not allowed to create this Opportunity");
        }
    }
    
    function validateDate($date, $format = 'Y-m-d') { 
	
	return $d && $d->format($format) === $date; 
} 
    
    function doPUT(){
        $opportunity = LIMSOpportunity::newFromId($this->getParam('id'));
        if($opportunity->isAllowedToEdit()){
            // Validate date
            $date = trim($this->POST('date'));
            $format = 'Y-m-d H:i:s';
            $d = DateTime::createFromFormat($format, $this->POST('date'));
            $dateValid = ($d && $d->format($format) === $date);
        
            $opportunity->owner = $this->POST('owner')->id;
            $opportunity->project = $this->POST('project')->id;
            $opportunity->userType = $this->POST('userType');
            $opportunity->category = $this->POST('category');
            $opportunity->surveyed = $this->POST('surveyed');
            $opportunity->responded = $this->POST('responded');
            $opportunity->satisfaction = $this->POST('satisfaction');
            $opportunity->status = $this->POST('status');
            $opportunity->description = $this->POST('description');
            $opportunity->products = $this->POST('products');
            if($dateValid){
                $opportunity->date = $date;
            }
            $opportunity->files = $this->POST('files');
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
