<?php

class ProjectAPI extends RESTAPI {
    
    function doGET(){
        $image = ($this->getParam('image') != "");
        $logo = ($this->getParam('logo') != "");
        if($this->getParam('id') != ""){
            $project = Project::newFromId($this->getParam('id'));
            if($this->getParam('id') == "-1"){
                $project->name = "Other";
            }
            if($project == null || $project->getName() == ""){
                $project = Project::newFromName($this->getParam('id'));
                if($project == null || $project->getName() == ""){
                    $this->throwError("This project does not exist");
                }
            }
            if($image){
                header('Content-Type: image/jpg');
                $photo = explode("/Photos/", $project->getPhoto(true));
                $content = file_get_contents("Photos/{$photo[1]}");
                echo $content;
                exit;
            }
            if($logo){
                header('Content-Type: image/png');
                $logo = explode("/Photos/", $project->getLogo(true));
                $content = file_get_contents("Photos/{$logo[1]}");
                echo $content;
                exit;
            }
            return $project->toJSON();
        }
        else{
            $projects = new Collection(Project::getAllProjectsEver());
            return $projects->toJSON();
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
