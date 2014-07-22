<?php

BackbonePage::register('Products', 'Products', 'network-tools', dirname(__FILE__));

class Products extends BackbonePage {
    
    function userCanExecute($user){
        return $user->isLoggedIn();
    }
    
    function getTemplates(){
        return array('Backbone/*',
                     'product_list', 
                     'product',
                     'product_edit',
                     'ManageProducts/manage_products_other_popup',
                     'ManageProducts/manage_products_projects_popup');
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
