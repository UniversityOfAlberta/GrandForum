<?php

class ThemeProjectsAPI extends RESTAPI {
    
    function doGET(){
        if($this->getParam('id') != ""){
            $theme = Theme::newFromId($this->getParam('id'));
            $projects = new Collection(array_values($theme->getProjects()));
            return $projects->toJSON();
        }
    }
    
    function doPOST(){
        
    }
    
    function doPUT(){

    }
    
    function doDELETE(){

    }
	
}

?>
