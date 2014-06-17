<?php

BackbonePage::register('MyProducts', 'MyProducts', 'network-tools', dirname(__FILE__));

class MyProducts extends BackbonePage {
    
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