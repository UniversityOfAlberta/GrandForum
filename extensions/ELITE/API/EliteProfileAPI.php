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
            else if($this->getParam('sciphd') != ""){
                $profile = PhDScienceEliteProfile::newFromUserId($this->getParam('id'));
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
            else if($this->getParam('sciphd') != ""){
                $profiles = new Collection(PhDScienceEliteProfile::getAllProfiles());
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
            else if($this->getParam('sciphd') != ""){
                $profiles = new Collection(PhDScienceEliteProfile::getAllProfiles());
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
        else if($this->getParam('sciphd') != ""){
            $profile = PhDScienceEliteProfile::newFromUserId($this->getParam('id'));
        }
        if(!$profile->exists()){
            $this->throwError("This profile does not exist");
        }
        if(!$profile->isAllowedToView()){
            $this->throwError("You are not allowed to edit this profile");
        }
        $_POST['file'] = $this->POST('file');
        $profile->status = $this->POST('status');
        $profile->comments = $this->POST('comments');
        $profile->matches = $this->POST('matches');
        $profile->update();
        
        $hire = $this->POST('hire');
        if(!empty($hire)){
            $_POST['hire'] = $hire;
            $match = $hire->match;
            $action = $hire->action;
            $profile->hires[$match] = $action; // Either Accepted or Rejected
            $profile->updateHires();
        }
        
        if($this->getParam('intern') != ""){
            $profile = InternEliteProfile::newFromUserId($this->getParam('id'));
        }
        else if($this->getParam('phd') != ""){
            $profile = PhDEliteProfile::newFromUserId($this->getParam('id'));
        }
        else if($this->getParam('sciphd') != ""){
            $profile = PhDScienceEliteProfile::newFromUserId($this->getParam('id'));
        }
        return $profile->toJSON();
    }
    
    function doDELETE(){
        
    }
	
}

?>
