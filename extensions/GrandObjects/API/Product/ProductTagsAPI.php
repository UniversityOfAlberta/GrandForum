<?php

class ProductTagsAPI extends RESTAPI {
    
    function doGET(){
        $tags = Product::getAllTags();
        return json_encode($tags);
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
