<?php

BackbonePage::register('GrantPage', 'GrantPage', 'network-tools', dirname(__FILE__));

class GrantPage extends BackbonePage {
    
    function isListed(){
        return false;
    }
    
    function userCanExecute($user){
        return true;
    }
    
    function getTemplates(){
        return array('grant',
                     'edit_grant');
    }
    
    function getViews(){
        return array('GrantView',
                     'EditGrantView');
    }
    
    function getModels(){
        return array();
    }

}

?>
