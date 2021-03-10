<?php

BackbonePage::register('Products', 'Products', 'network-tools', dirname(__FILE__));

class Products extends BackbonePage {
    
    function userCanExecute($user){
        global $config;
        if($config->getValue('guestLockdown') && !$user->isLoggedIn()){
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
