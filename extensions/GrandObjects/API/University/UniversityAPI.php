<?php

class UniversityAPI extends RESTAPI {
    
    function doGET(){
        $id = $this->getParam('id');
        if($id != ""){
            $page = University::newFromId($id);
            return $page->toJSON();
        }
        else{
            $unis = new Collection(University::getAllUniversities());
            return $unis->toJSON();
        }
        return $page->toJSON();
    }
    
    function doPOST(){
        return $this->doGet();
    }
    
    function doPUT(){
        return $this->doGet();
    }
    
    function doDELETE(){
        return $this->doGet();
    }
	
}

?>
