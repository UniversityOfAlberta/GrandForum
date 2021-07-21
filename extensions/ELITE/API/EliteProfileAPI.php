<?php

class EliteProfileAPI extends RESTAPI {
    
    function doGET(){
        if($this->getParam('id') != ""){
            $profile = EliteProfile::newFromId($this->getParam('id'));
            return $profile->toJSON();
        }
        else{
            $profiles = new Collection(EliteProfile::getAllProfiles());
            return $profiles->toJSON();
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
