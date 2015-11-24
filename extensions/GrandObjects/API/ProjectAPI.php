<?php

class ProjectAPI extends RESTAPI {
    
    function doGET(){
        if($this->getParam('id') != ""){
            $project = Project::newFromId($this->getParam('id'));
            if($project == null || $project->getName() == ""){
                $project = Project::newFromName($this->getParam('id'));
                if($project == null || $project->getName() == ""){
                    $this->throwError("This project does not exist");
                }
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

class ProjectMembersAPI extends RESTAPI {
    
    function doGET(){
        if($this->getParam('id') != ""){
            $project = Project::newFromId($this->getParam('id'));
            if($project == null || $project->getName() == ""){
                $project = Project::newFromName($this->getParam('id'));
                if($project == null || $project->getName() == ""){
                    $this->throwError("This project does not exist");
                }
            }
            if($this->getParam('role') != ""){
                $exploded = explode(",", $this->getParam('role'));
                $finalPeople = array();
                foreach($exploded as $role){
                    $role = trim($role);
                    $people = $project->getAllPeople($role);
                    foreach($people as $person){
                        $finalPeople[$person->getReversedName()] = $person;
                    }
                }
                ksort($finalPeople);
                $finalPeople = new Collection(array_values($finalPeople));
                return $finalPeople->toJSON();
            }
            else{
                $finalPeople = array();
                $people = $project->getAllPeople();
                foreach($people as $person){
                    $finalPeople[$person->getReversedName()] = $person;
                }
                ksort($finalPeople);
                $finalPeople = new Collection(array_values($finalPeople));
                return $finalPeople->toJSON();
            }
        }
    }
    
    function doPOST(){
        
    }
    
    function doPUT(){

    }
    
    function doDELETE(){

    }
	
}

class ProjectProductAPI extends RESTAPI {
    
    function doGET(){
        if($this->getParam(0) == "project"){
            $project = Project::newFromId($this->getParam('id'));
            $json = array();
            $products = $project->getPapers("all");
            foreach($products as $product){
                $array = array('productId' => $product->getId(), 
                               'projectId'=> $this->getParam('id'),
                               'startDate' => $product->getDate(),
                               'endDate' => $product->getDate());
                if($this->getParam('productId') != null && $product->getId() == $this->getParam('productId')){
                    return json_encode($array);
                }
                else if($this->getParam('productId') == null){
                    $json[] = $array;
                }
            }
            return json_encode($json);
        }
        else if($this->getParam(0) == "product"){
            $product = Paper::newFromId($this->getParam('id'));
            $json = array();
            $projects = $product->getProjects(); 
            foreach($projects as $project){
                if($project->getId()){
                    $array = array('productId' => $this->getParam('id'), 
                                   'projectId' => $project->getId(),
                                   'startDate' => $product->getDate(),
                                   'endDate' => $product->getDate());
                    if($this->getParam('projectId') != null && $project->getId() == $this->getParam('projectId')){
                        return json_encode($array);
                    }
                    else if($this->getParam('project') == null){
                        $json[] = $array;
                    }
                }
            }
            return json_encode($json);
        }
        return null;
    }
    
    function doPOST(){
        global $wgUser;
        if($wgUser->isLoggedIn()){
            if($this->getParam(0) == "project"){
                $project = Project::newFromId($this->getParam('id'));
                $product = Paper::newFromId($this->getParam('productId'));
            }
            else if($this->getParam(0) == "product"){
                $product = Paper::newFromId($this->getParam('id'));
                $project = Project::newFromId($this->getParam('projectId'));
            }            
            $projects = $product->getProjects();
            $serializedProjects = array();
            $found = false;
            foreach($projects as $p){
                if($p->getId() == $project->getId()){
                    $found = true;
                }
                $serializedProjects[] = $p->getId();
            }
            if(!$found){
                $serializedProjects[] = $project->getId();
                DBFunctions::insert('grand_product_projects',
                                    array('product_id' => $product->getId()),
                                    array('project_id' => $project->getId()));
                Product::$cache = array();
                Product::$dataCache = array();
            }
        }
        else{
            $this->throwError("Project was not added");
        }
        return $this->doGET();
    }
    
    function doPUT(){
        return $this->doPOST();
    }
    
    function doDELETE(){
        global $wgUser;
        if($wgUser->isLoggedIn()){
            if($this->getParam(0) == "project"){
                $project = Project::newFromId($this->getParam('id'));
                $product = Paper::newFromId($this->getParam('productId'));
            }
            else if($this->getParam(0) == "product"){
                $product = Paper::newFromId($this->getParam('id'));
                $project = Project::newFromId($this->getParam('projectId'));
            }
            $serializedProjects = array();
            $projects = $product->getProjects();
            foreach($projects as $p){
                if($p->getId() != $project->getId()){
                    $serializedProjects[] = $p->getId();
                }
            }
            DBFunctions::delete('grand_product_projects',
                                array('product_id' => $product->getId()),
                                array('project_id' => $project->getId()));
            return;
        }
        else{
            $this->throwError("Author was not deleted");
        }
    }   
    
}

?>
