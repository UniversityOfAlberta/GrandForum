<?php

class DiversityAPI extends RESTAPI {
    
    function doGET(){
        $me = Person::newFromWgUser();
        $diversity = Diversity::newFromUserId($me->getId());
        if(!$diversity->canView()){
            $this->throwError("You are not allowed to view this Diversity Survey");
        }
        return $diversity->toJSON();
    }
    
    function doPOST(){
        $me = Person::newFromWgUser();
        if(!$me->isLoggedIn()){
            $this->throwError("You must me logged in to create a Diversity Survey");
        }
        $diversity = new Diversity(array());
        $diversity->userId = $me->getId();
        $diversity->language = $this->POST('language');
        $diversity->decline = $this->POST('decline');
        $diversity->reason = $this->POST('reason');
        $diversity->gender = $this->POST('gender');
        $diversity->orientation = $this->POST('orientation');
        $diversity->indigenous = $this->POST('indigenous');
        $diversity->disability = $this->POST('disability');
        $diversity->disabilityVisibility = $this->POST('disabilityVisibility');
        $diversity->minority = $this->POST('minority');
        $diversity->race = $this->POST('race');
        $diversity->languageMinority = $this->POST('languageMinority');
        $diversity->immigration = $this->POST('immigration');
        $diversity->affiliation = $this->POST('affiliation');
        $diversity->age = $this->POST('age');
        $diversity->indigenousApply = $this->POST('indigenousApply');
        $diversity->trueSelf = $this->POST('trueSelf');
        $diversity->valued = $this->POST('valued');
        $diversity->space = $this->POST('space');
        $diversity->respected = $this->POST('respected');
        $diversity->leastRespected = $this->POST('leastRespected');
        $diversity->principles = $this->POST('principles');
        $diversity->principlesDescribe = $this->POST('principlesDescribe');
        $diversity->statement = $this->POST('statement');
        $diversity->improve = $this->POST('improve');
        $diversity->training = $this->POST('training');
        $diversity->preventsTraining = $this->POST('preventsTraining');
        $diversity->trainingTaken = $this->POST('trainingTaken');
        $diversity->implemented = $this->POST('implemented');
        $diversity->stem = $this->POST('stem');
        $diversity->comments = $this->POST('comments');
        $diversity->create();
        return $diversity->toJSON();
    }
    
    function doPUT(){
        $me = Person::newFromWgUser();
        if(!$me->isLoggedIn()){
            $this->throwError("You must me logged in to update a Diversity Survey");
        }
        $diversity = Diversity::newFromUserId($me->getId());
        if(!$diversity->canView()){
            $this->throwError("You are not allowed to view this Diversity Survey");
        }
        $diversity->language = $this->POST('language');
        $diversity->decline = $this->POST('decline');
        $diversity->reason = $this->POST('reason');
        $diversity->gender = $this->POST('gender');
        $diversity->orientation = $this->POST('orientation');
        $diversity->indigenous = $this->POST('indigenous');
        $diversity->disability = $this->POST('disability');
        $diversity->disabilityVisibility = $this->POST('disabilityVisibility');
        $diversity->minority = $this->POST('minority');
        $diversity->race = $this->POST('race');
        $diversity->languageMinority = $this->POST('languageMinority');
        $diversity->immigration = $this->POST('immigration');
        $diversity->affiliation = $this->POST('affiliation');
        $diversity->age = $this->POST('age');
        $diversity->indigenousApply = $this->POST('indigenousApply');
        $diversity->trueSelf = $this->POST('trueSelf');
        $diversity->valued = $this->POST('valued');
        $diversity->space = $this->POST('space');
        $diversity->respected = $this->POST('respected');
        $diversity->leastRespected = $this->POST('leastRespected');
        $diversity->principles = $this->POST('principles');
        $diversity->principlesDescribe = $this->POST('principlesDescribe');
        $diversity->statement = $this->POST('statement');
        $diversity->improve = $this->POST('improve');
        $diversity->training = $this->POST('training');
        $diversity->preventsTraining = $this->POST('preventsTraining');
        $diversity->trainingTaken = $this->POST('trainingTaken');
        $diversity->implemented = $this->POST('implemented');
        $diversity->stem = $this->POST('stem');
        $diversity->comments = $this->POST('comments');
        $diversity->update();
        return $diversity->toJSON();
    }
    
    function doDELETE(){
        return $this->doGET();
    }
	
}

?>
