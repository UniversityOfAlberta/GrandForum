<?php

BackbonePage::register('FreezePage', 'Freeze', 'network-tools', dirname(__FILE__));

class FreezePage extends BackbonePage {
    
    function isListed(){
        return false;
    }
    
    function userCanExecute($user){
        return true;
    }
    
    function getTemplates(){
        return array('freeze');
    }
    
    function getViews(){
        return array('FreezeView');
    }
    
    function getModels(){
        return array();
    }

}

?>
