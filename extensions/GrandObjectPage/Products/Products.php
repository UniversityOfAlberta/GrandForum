<?php

BackbonePage::register('Products', 'Products', 'network-tools', dirname(__FILE__));

class Products extends BackbonePage {
    
    function userCanExecute($user){
        global $config;
        if($config->getValue('guestLockdown') && !$user->isRegistered()){
            return false;
        }
        return true;
    }
    
    function getTemplates(){
        return array('Backbone/*',
                     'product_list', 
                     'product',
                     'product_edit');
    }
    
    function getViews(){
        global $wgOut;
        $emptyProject = new Project(array());
        $publicationsFrozen = json_encode($emptyProject->isFeatureFrozen("Publications"));
        
        $wgOut->addScript("<script type='text/javascript'>
            var publicationsFrozen = $publicationsFrozen;
        </script>");
        
        return array('Backbone/*',
                     'ProductListView', 
                     'ProductView',
                     'ProductEditView');
    }
    
    function getModels(){
        return array('Backbone/*');
    }

}

?>
