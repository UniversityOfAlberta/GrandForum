<?php

class ProductAPI extends RESTAPI {
    
    function doGET(){
        if($this->getParam('id') != "" && count(explode(",", $this->getParam('id'))) == 1){
            $paper = Paper::newFromId($this->getParam('id'));
            if($paper == null || $paper->getTitle() == ""){
                $this->throwError("This product does not exist");
            }
            return $paper->toJSON();
        }
        else if($this->getParam('id') != "" && count(explode(",", $this->getParam('id'))) > 1){
            $json = array();
            $papers = Product::newFromIds(explode(",", $this->getParam('id')), false);
            foreach($papers as $paper){
                $json[] = $paper->toArray();
            }
            return large_json_encode($json);
        }
        else{
            $json = array();
            if($this->getParam('category') != "" && 
               $this->getParam('projectId') != "" &&
               $this->getParam('grand')){
                $papers = Paper::getAllPapers($this->getParam('projectId'), 
                                              $this->getParam('category'), 
                                              $this->getParam('grand'));
            }
            else{
                $papers = Paper::getAllPapers('all', 'all', 'both');
            }
            $start = 0;
            $count = 999999999;
            if($this->getParam('start') != "" &&
               $this->getParam('count') != ""){
                $start = $this->getParam('start');
                $count = $this->getParam('count');
            }
            $i = 0;
            foreach($papers as $id => $paper){
                if($i >= $start && $i < $start + $count){
                    $json[] = $paper->toArray();
                }
                $i++;
            }
            return large_json_encode($json);
        }
    }
    
    function doPOST(){
        $paper = new Paper(array());
        header('Content-Type: application/json');
        $paper->title = $this->POST('title');
        $paper->category = $this->POST('category');
        $paper->type = $this->POST('type');
        $paper->description = $this->POST('description');
        $paper->date = $this->POST('date');
        $paper->status = $this->POST('status');
        $paper->authors = $this->POST('authors');
        $paper->projects = $this->POST('projects');
        $paper->data = (array)($this->POST('data'));
        $paper->access_id = $this->POST('access_id');
        $paper->access = $this->POST('access');
        $status = $paper->create();
        if(!$status){
            $this->throwError("The product <i>{$paper->getTitle()}</i> could not be created");
        }
        $paper = Product::newFromId($paper->getId());
        return $paper->toJSON();
    }
    
    function doPUT(){
        $paper = Product::newFromId($this->getParam('id'));
        if($paper == null || $paper->getTitle() == ""){
            $this->throwError("This product does not exist");
        }
        header('Content-Type: application/json');
        $paper->title = $this->POST('title');
        $paper->category = $this->POST('category');
        $paper->type = $this->POST('type');
        $paper->description = $this->POST('description');
        $paper->date = $this->POST('date');
        $paper->status = $this->POST('status');
        $paper->authors = $this->POST('authors');
        $paper->projects = $this->POST('projects');
        $paper->data = (array)($this->POST('data'));
        $paper->access_id = $this->POST('access_id');
        $paper->access = $this->POST('access');
        $status = $paper->update();
        if(!$status){
            $this->throwError("The product <i>{$paper->getTitle()}</i> could not be updated");
        }
        $paper = Product::newFromId($this->getParam('id'));
        return $paper->toJSON();
    }
    
    function doDELETE(){
        $paper = Paper::newFromId($this->getParam('id'));
        if($paper == null || $paper->getTitle() == ""){
            $this->throwError("This product does not exist");
        }
        header('Content-Type: application/json');
        $status = $paper->delete();
        if($paper->getAccessId() > 0 && $status){
            $paper->deleted = "1";
            return $paper->toJSON();
        }
        return $this->doGET();
    }
	
}

?>
