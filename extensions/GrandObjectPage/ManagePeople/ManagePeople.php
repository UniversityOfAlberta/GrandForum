<?php

BackbonePage::register('ManagePeople', 'ManagePeople', 'network-tools', dirname(__FILE__));

class ManagePeople extends BackbonePage {
    
    function isListed(){
        return false;
    }
    
    function userCanExecute($user){
        return true;
    }
    
    function getTemplates(){
        return array();
    }
    
    function getViews(){
        return array();
    }
    
    function getModels(){
        return array();
    }

}

?>
