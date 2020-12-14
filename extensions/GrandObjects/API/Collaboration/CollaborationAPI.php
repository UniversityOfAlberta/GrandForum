<?php

class CollaborationAPI extends RESTAPI {
    
    function doGET(){
        $leverages = ($this->getParam('leverages') != "");
        if($this->getParam('id') != ""){
            $collab = Collaboration::newFromId($this->getParam('id'));
            if($this->getParam('file') != ""){
                $files = $collab->files;
                if(isset($files[$this->getParam('file')])){
                    $file = $files[$this->getParam('file')];
                    header('Content-Type: '.$file->type);
                    header('Content-Disposition: attachment; filename="'.$file->filename.'"');
                    $exploded = explode(",", $file->data);
                    echo base64_decode($exploded[1]);
                }
                else{
                    $this->throwError("The collaboration <i>{$collab->getTitle()}</i> does not have a file by the id of {$this->getParam('file')}");
                }
                exit;
            }
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
        $emptyProject = new Project(array());
        $leveragesFrozen = $emptyProject->isFeatureFrozen("Leverages");
        $collaborationsFrozen = $emptyProject->isFeatureFrozen("Collaborations");
        
        if($this->POST('leverage') == true && $leveragesFrozen){
            $this->throwError("Leverages have been Frozen");
        }
        else if($this->POST('leverage') == false && $collaborationsFrozen){
            $this->throwError("Collaborations have been frozen");
        }
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
        $collab->extra = $this->POST('extra');
        $collab->knowledgeUser = $this->POST('knowledgeUser');
        $collab->leverage = $this->POST('leverage');
        $collab->projects = $this->POST('projects');
        $collab->files = $this->POST('files');
        $status = $collab->create();
        if(!$status) {
            $this->throwError("Could not create collaboration");
        }
        return $collab->toJSON();
    }
    
    function doPUT(){
        $emptyProject = new Project(array());
        $leveragesFrozen = $emptyProject->isFeatureFrozen("Leverages");
        $collaborationsFrozen = $emptyProject->isFeatureFrozen("Collaborations");
        
        if($this->POST('leverage') == true && $leveragesFrozen){
            $this->throwError("Leverages have been Frozen");
        }
        else if($this->POST('leverage') == false && $collaborationsFrozen){
            $this->throwError("Collaborations have been frozen");
        }
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
        $collab->extra = $this->POST('extra');
        $collab->knowledgeUser = $this->POST('knowledgeUser');
        $collab->leverage = $this->POST('leverage');
        $collab->projects = $this->POST('projects');
        $collab->files = $this->POST('files');
        $status = $collab->update();
        if(!$status) {
            $this->throwError("Could not create collaboration");
        }
        return $collab->toJSON();
    }
    
    function doDELETE(){
        $emptyProject = new Project(array());
        $leveragesFrozen = $emptyProject->isFeatureFrozen("Leverages");
        $collaborationsFrozen = $emptyProject->isFeatureFrozen("Collaborations");

        $collab = Collaboration::newFromId($this->getParam('id'));
        if($collab->leverage == true && $leveragesFrozen){
            $this->throwError("Leverages have been Frozen");
        }
        else if($this->leverage == false && $collaborationsFrozen){
            $this->throwError("Collaborations have been frozen");
        }
        $collab = $collab->delete();
        return $collab->toJSON();
    }
	
}

?>
