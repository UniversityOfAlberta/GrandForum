<?php

class ProductCitationAPI extends RESTAPI {
    
    function doGET(){
        $paper = Paper::newFromId($this->getParam('id'));
        if($paper == null || $paper->getTitle() == ""){
            $this->throwError("This product does not exist");
        }
        return json_encode($paper->getCitation(true, true, true, true));
    }
    
    function doPOST(){
        return $this->doGET();
    }
    
    function doPUT(){
        return $this->doGET();
    }
    
    function doDELETE(){
        return $this->doGET();
    }
	
}

?>
