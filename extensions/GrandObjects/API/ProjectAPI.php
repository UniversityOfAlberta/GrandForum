<?php

class ProjectAPI extends RESTAPI {

    var $id;
    var $action;

    function processParams($params){
        $this->id = @$params[1];
        $this->action = @$params[2];
    }
    
    function doGET(){
        if($this->id != ""){
            $project = Project::newFromId($this->id);
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
