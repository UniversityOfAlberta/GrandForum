<?php

class ProjectAPI extends RESTAPI {
    
    function doGET(){
        if($this->getParam('id') != ""){
            $project = Project::newFromId($this->getParam('id'));
            if($project == null || $project->getName() == ""){
                $this->throwError("This project does not exist");
            }
            return $project->toJSON();
        }
        else{
            $json = array();
            $projects = Project::getAllProjects();
            foreach($projects as $project){
                $json[] = $project->toArray();
            }
            return json_encode($json);
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
