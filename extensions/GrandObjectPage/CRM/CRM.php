<?php

BackbonePage::register('CRM', 'CRM', 'network-tools', dirname(__FILE__));

class CRM extends BackbonePage {
    
    function userCanExecute($user){
        return true;
    }
    
    function getTemplates(){
        return array('Backbone/*');
    }
    
    function getViews(){
        return array('Backbone/*');
    }
    
    function getModels(){
        return array('Backbone/*');
    }

}

?>
