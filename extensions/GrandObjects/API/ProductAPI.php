<?php

class ProductAPI extends RESTAPI {
    
    function doGET(){
        if($this->getParam('id') != ""){
            $paper = Paper::newFromId($this->id);
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
        $paper = Paper::newFromId($this->id);
        header('Content-Type: application/json');
        $paper->create();
    }
    
    function doPUT(){
        $paper = Paper::newFromId($this->id);
        if($paper == null || $paper->getName() == ""){
            $this->throwError("This product does not exist");
        }
        header('Content-Type: application/json');
        $paper->update();
    }
    
    function doDELETE(){
        $paper = Paper::newFromId($this->id);
        if($paper == null || $paper->getName() == ""){
            $this->throwError("This product does not exist");
        }
        header('Content-Type: application/json');
        $paper->delete();
    }
	
}

?>
