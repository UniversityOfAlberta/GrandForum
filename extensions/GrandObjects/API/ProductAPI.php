<?php

class ProductAPI extends RESTAPI {
    
    function doGET(){
        if($this->getParam('id') != ""){
            $paper = Paper::newFromId($this->getParam('id'));
            if($paper == null || $paper->getTitle() == ""){
                $this->throwError("This product does not exist");
            }
            return $paper->toJSON();
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
            foreach($papers as $paper){
                $json[] = $paper->toArray();
            }
            return json_encode($json);
        }
    }
    
    function doPOST(){
        $paper = Paper::newFromId($this->getParam('id'));
        header('Content-Type: application/json');
        $paper->create();
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
        $status = $paper->update();
        if(!$status){
            $this->throwError("The product <i>{$product->getTitle()}</i> could not be updated");
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
        $paper->delete();
        return $this->doGET();
    }
	
}

?>
