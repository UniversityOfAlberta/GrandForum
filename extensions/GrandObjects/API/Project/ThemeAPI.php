<?php

class ThemeAPI extends RESTAPI {
    
    function doGET(){
        if($this->getParam('id') != ""){
            $theme = Theme::newFromId($this->getParam('id'));
            return json_encode($theme);
        }
        $themes = Theme::getAllThemes();
        return json_encode($themes);
    }
    
    function doPOST(){
        
    }
    
    function doPUT(){

    }
    
    function doDELETE(){

    }
	
}

?>
