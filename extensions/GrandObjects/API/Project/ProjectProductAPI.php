<?php

class ProjectProductAPI extends RESTAPI {
    
    function doGET(){
        if($this->getParam(0) == "project"){
            $project = Project::newFromId($this->getParam('id'));
            if($project == null || $project->getId() == 0){
                $this->throwError("This Project does not exist");
            }
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
            if($product == null || $product->getId() == 0){
                $this->throwError("This Product does not exist");
            }
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
                    else if($this->getParam('projectId') == null){
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
            if($project == null || $project->getId() == 0){
                $this->throwError("This Project does not exist");
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
            if($project == null || $project->getId() == 0){
                $this->throwError("This Project does not exist");
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
