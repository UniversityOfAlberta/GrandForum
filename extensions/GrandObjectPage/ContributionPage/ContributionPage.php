<?php

BackbonePage::register('ContributionPage', 'ContributionPage', 'network-tools', dirname(__FILE__));

class ContributionPage extends BackbonePage {
    
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