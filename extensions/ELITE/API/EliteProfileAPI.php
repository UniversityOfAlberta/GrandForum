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
        $this->doPUT();
    }
    
    function doPUT(){
        $profile = EliteProfile::newFromUserId($this->getParam('id'));
        if(!$profile->exists()){
            $this->throwError("This profile does not exist");
        }
        $profile->status = $this->POST('status');
        $profile->comments = $this->POST('comments');
        $profile->update();
        return $profile->toJSON();
    }
    
    function doDELETE(){
        
    }
	
}

?>
