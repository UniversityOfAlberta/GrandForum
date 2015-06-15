<?php

class WikiPageAPI extends RESTAPI {
    
    function doGET(){
        $id = $this->getParam('id');
        if($id != ""){
            $page = Wiki::newFromId($id);
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
