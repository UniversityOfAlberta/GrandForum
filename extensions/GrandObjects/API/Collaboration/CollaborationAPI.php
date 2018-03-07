<?php

class CollaborationAPI extends RESTAPI {
    
    function doGET(){
        if($this->getParam('id') != ""){
            $collab = Collaboration::newFromId($this->getParam('id'));
            return $collab->toJSON();
        }
        /*
        else if($this->getParam('person_id') != "" ){
            $person = Person::newFromId($this->getParam('person_id'));
            $collabs = new Collection($person->getCollaborations());
            return $collabs->toJSON();
        }
        */
        else{
            $collabs = new Collection(Collaboration::getAllCollaborations());
            return $collabs->toJSON();
        }
    }
    
    function doPOST(){
        $collab = new Collaboration(array());
        $collab->title = $this->POST('title');
        $collab->sector = $this->POST('sector');
        $collab->country = $this->POST('country');
        $collab->planning = $this->POST('planning');
        $collab->designDataCollection = $this->POST('designDataCollection');
        $collab->analysisOfResults = $this->POST('analysisOfResults');
        $collab->exchangeKnowledge = $this->POST('exchangeKnowledge');
        $collab->userKnowledge = $this->POST('userKnowledge');
        $collab->other = $this->POST('other');
        $collab->personName = $this->POST('personName');
        $collab->position = $this->POST('position');
        $status = $collab->create();
        if(!$status) {
            $this->throwError("Could not create collaboration");
        }
        return $collab->toJSON();
    }
    
    function doPUT(){
        $collab = Collaboration::newFromId($this->getParam('id'));
        $collab->title = $this->POST('title');
        $collab->sector = $this->POST('sector');
        $collab->country = $this->POST('country');
        $collab->planning = $this->POST('planning');
        $collab->designDataCollection = $this->POST('designDataCollection');
        $collab->analysisOfResults = $this->POST('analysisOfResults');
        $collab->exchangeKnowledge = $this->POST('exchangeKnowledge');
        $collab->userKnowledge = $this->POST('userKnowledge');
        $collab->other = $this->POST('other');
        $collab->personName = $this->POST('personName');
        $collab->position = $this->POST('position');
        $status = $collab->update();
        if(!$status) {
            $this->throwError("Could not create collaboration");
        }
        return $collab->toJSON();
    }
    
    function doDELETE(){
        $collab = Collaboration::newFromId($this->getParam('id'));
        $collab = $collab->delete();
        return $collab->toJSON();
    }
	
}

?>
