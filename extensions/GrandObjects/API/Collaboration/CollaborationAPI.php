<?php

class CollaborationAPI extends RESTAPI {
    
    function doGET(){
        $leverages = ($this->getParam('leverages') != "");
        if($this->getParam('id') != ""){
            $collab = Collaboration::newFromId($this->getParam('id'));
            return $collab->toJSON();
        }
        else if($leverages){
            $collabs = new Collection(Collaboration::getAllCollaborations(1));
            return $collabs->toJSON();
        }
        else{
            $collabs = new Collection(Collaboration::getAllCollaborations(0));
            return $collabs->toJSON();
        }
    }
    
    function doPOST(){
        $collab = new Collaboration(array());
        $collab->title = $this->POST('title');
        $collab->endYear = $this->POST('endYear');
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
        $collab->email = $this->POST('email');
        $collab->cash = $this->POST('cash');
        $collab->inkind = $this->POST('inkind');
        $collab->projectedCash = $this->POST('projectedCash');
        $collab->projectedInkind = $this->POST('projectedInkind');
        $collab->existed = $this->POST('existed');
        $collab->knowledgeUser = $this->POST('knowledgeUser');
        $collab->leverage = $this->POST('leverage');
        $collab->projects = $this->POST('projects');
        $status = $collab->create();
        if(!$status) {
            $this->throwError("Could not create collaboration");
        }
        return $collab->toJSON();
    }
    
    function doPUT(){
        $collab = Collaboration::newFromId($this->getParam('id'));
        $collab->title = $this->POST('title');
        $collab->endYear = $this->POST('endYear');
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
        $collab->email = $this->POST('email');
        $collab->cash = $this->POST('cash');
        $collab->inkind = $this->POST('inkind');
        $collab->projectedCash = $this->POST('projectedCash');
        $collab->projectedInkind = $this->POST('projectedInkind');
        $collab->existed = $this->POST('existed');
        $collab->knowledgeUser = $this->POST('knowledgeUser');
        $collab->leverage = $this->POST('leverage');
        $collab->projects = $this->POST('projects');
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
