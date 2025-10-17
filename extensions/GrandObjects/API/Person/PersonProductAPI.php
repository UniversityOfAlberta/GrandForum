<?php

class PersonProductAPI extends RESTAPI {
    
    function doGET(){
        if($this->getParam(0) == "person"){
            // Get Products
            $me = Person::newFromWgUser();
            $person = Person::newFromId($this->getParam('id'));
            $json = array();
            $onlyPublic = true;
            $showAll = false;
            $showManaged = false;
            $products = array();
            if($this->getParam(3) == "private"){
                $onlyPublic = false;
            }
            if($this->getParam(3) == "managed"){
                $onlyPublic = false;
                $showManaged = true;
            }
            if($this->getParam(3) == "all" && $me->isRoleAtLeast(STAFF) && $me->getId() == $person->getId()){
                $showAll = true;
                $onlyPublic = false;
            }
            
            if($showAll){
                $products = Product::getAllPapers("all", true, 'both', $onlyPublic, 'Public');
            }
            else if($showManaged){
                $projectId = $this->getParam('projectId');
                $projects = array();
                if($projectId == ""){
                    foreach($person->getPapers("all", true, 'both', $onlyPublic, 'Public') as $p){
                        $products[$p->getId()] = $p;
                    }
                    $projects = $person->getProjects();
                }
                else{
                    $project = Project::newFromId($projectId);
                    if($project != null){
                        $projects = array($project);
                    }
                }
                foreach($projects as $project){
                    if($person->isRole(PL, $project) || $person->isRole(PA, $project) || $person->isRoleAtLeast(STAFF)){
                        // Leader should see all related publications
                        foreach($project->getPapers("all", "0000-00-00", EOT, $onlyPublic) as $p){
                            $products[$p->getId()] = $p;
                        }
                    }
                    else{
                        // Non-leader should only see their own
                        foreach($person->getPapers("all", true, 'both', $onlyPublic, 'Public') as $p){
                            if($p->belongsToProject($project)){
                                $products[$p->getId()] = $p;
                            }
                        }
                        $projects = $person->getProjects();
                    }
                }
            }
            else{
                $products = $person->getPapers("all", true, 'both', $onlyPublic, 'Public');
            }
            foreach($products as $product){
                $array = array('productId' => $product->getId(), 
                               'personId'=> $this->getParam('id'),
                               'startDate' => $product->getDate(),
                               'endDate' => $product->getDate());
                if($this->getParam('productId') != null && $product->getId() == $this->getParam('productId')){
                    return json_encode($array);
                }
                else if($this->getParam('productId') == null){
                    $json[] = $array;
                }
            }
            if($this->getParam('bibtex') != ""){
                header('Content-Type: text/plain');
                $collection = new Collection($products);
                echo implode("", $collection->pluck('toBibTeX()'));
                close();
            }
            return json_encode($json);
        }
        else if($this->getParam(0) == "product"){
            // Get Authors
            $product = Paper::newFromId($this->getParam('id'));
            $json = array();
            $authors = $product->getAuthors(); 
            foreach($authors as $author){
                if($author->getId()){
                    $array = array('productId' => $this->getParam('id'), 
                                   'personId' => $author->getId(),
                                   'startDate' => $product->getDate(),
                                   'endDate' => $product->getDate());
                    if($this->getParam('personId') != null && $author->getId() == $this->getParam('personId')){
                        return json_encode($array);
                    }
                    else if($this->getParam('personId') == null){
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
        if($wgUser->isRegistered()){
            if($this->getParam(0) == "person"){
                $person = Person::newFromId($this->getParam('id'));
                $product = Paper::newFromId($this->getParam('productId'));
            }
            else if($this->getParam(0) == "product"){
                $product = Paper::newFromId($this->getParam('id'));
                $person = Person::newFromId($this->getParam('personId'));
            }
            $serializedAuthors = $product->authors;
            $authors = $product->getAuthors();
            $found = false;
            foreach($authors as $author){
                if($author->getId() == $person->getId()){
                    $found = true;
                }
            }
            if(!$found){
                $authors = unserialize($serializedAuthors);
                $authors[] = $person->getId();
                DBFunctions::update('grand_products',
                                    array('authors' => serialize($authors)),
                                    array('id' => $product->getId()));
                Paper::$cache = array();
                Paper::$dataCache = array();
            }
        }
        else{
            $this->throwError("Author was not added");
        }
        return $this->doGET();
    }
    
    function doPUT(){
        return $this->doPOST();
    }
    
    function doDELETE(){
        global $wgUser;
        if($wgUser->isRegistered()){
            if($this->getParam(0) == "person"){
                $person = Person::newFromId($this->getParam('id'));
                $product = Paper::newFromId($this->getParam('productId'));
            }
            else if($this->getParam(0) == "product"){
                $product = Paper::newFromId($this->getParam('id'));
                $person = Person::newFromId($this->getParam('personId'));
            }
            $serializedAuthors = $product->authors;
            $authors = $product->getAuthors();
            foreach($authors as $key => $author){
                if($author->getId() == $person->getId()){
                    $serializedAuthors = unserialize($serializedAuthors);
                    unset($serializedAuthors[$key]);
                    DBFunctions::update('grand_products',
                                        array('authors' => serialize($serializedAuthors)),
                                        array('id' => $product->getId()));
                    return;
                }
            }
        }
        else{
            $this->throwError("Author was not deleted");
        }
    }
    
}

?>
