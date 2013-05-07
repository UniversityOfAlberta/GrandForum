<?php

class NewSearchAPI extends RESTAPI {
    
    function doGET(){
       /* if($this->getParam('id') != ""){
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
        }*/
        return "";
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
