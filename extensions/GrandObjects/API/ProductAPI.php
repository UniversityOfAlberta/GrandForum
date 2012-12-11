<?php

class ProductAPI extends RESTAPI {

    var $id;

    function processParams($params){
        foreach($params as $key => $param){
            if($key == 1){
                $this->id = $param;
            }
        }
    }
    
    function doGET(){
        $paper = Paper::newFromId($this->id);
        if($paper == null || $paper->getTitle() == ""){
            $this->throwError("This product does not exist");
        }
        return $paper->toJSON();
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
