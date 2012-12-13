<?php

class ProductAPI extends RESTAPI {

    var $id;

    function processParams($params){
        $this->id = @$params[1];

    }

    function isLoginRequired(){
        return true;

    }
    
    function doGET(){
        if($this->id != ""){
            $paper = Paper::newFromId($this->id);
            if($paper == null || $paper->getTitle() == ""){
                $this->throwError("This product does not exist");
            }
            return $paper->toJSON();
        }
        else{
            $json = array();
            $papers = Paper::getAllPapers('all', 'all', 'both');
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
