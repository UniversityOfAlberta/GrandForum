<?php

class EliteProfileAPI extends RESTAPI {
    
    function doGET(){
        if($this->getParam('id') != ""){
            if($this->getParam('intern') != ""){
                $profile = InternEliteProfile::newFromUserId($this->getParam('id'));
            }
            else if($this->getParam('phd') != ""){
                $profile = PhDEliteProfile::newFromUserId($this->getParam('id'));
            }
            return $profile->toJSON();
        }
        else if($this->getParam('matched') != ""){
            if($this->getParam('intern') != ""){
                $profiles = new Collection(InternEliteProfile::getAllMatchedProfiles());
            }
            else if($this->getParam('phd') != ""){
                $profiles = new Collection(PhDEliteProfile::getAllMatchedProfiles());
            }
            return $profiles->toJSON();
        }
        else{
            if($this->getParam('intern') != ""){
                $profiles = new Collection(InternEliteProfile::getAllProfiles());
            }
            else if($this->getParam('phd') != ""){
                $profiles = new Collection(PhDEliteProfile::getAllProfiles());
            }
            return $profiles->toJSON();
        }
    }
    
    function doPOST(){
        $this->doPUT();
    }
    
    function doPUT(){
        if($this->getParam('intern') != ""){
            $profile = InternEliteProfile::newFromUserId($this->getParam('id'));
        }
        else if($this->getParam('phd') != ""){
            $profile = PhDEliteProfile::newFromUserId($this->getParam('id'));
        }
        if(!$profile->exists()){
            $this->throwError("This profile does not exist");
        }
        $profile->status = $this->POST('status');
        $profile->comments = $this->POST('comments');
        $profile->matches = $this->POST('matches');
        $profile->update();
        if($this->getParam('intern') != ""){
            $profile = InternEliteProfile::newFromUserId($this->getParam('id'));
        }
        else if($this->getParam('phd') != ""){
            $profile = PhDEliteProfile::newFromUserId($this->getParam('id'));
        }
        return $profile->toJSON();
    }
    
    function doDELETE(){
        
    }
	
}

?>
