<?php

BackbonePage::register('Products', 'Products', 'grand-tools', dirname(__FILE__));

class Products extends BackbonePage {
    
    function getTemplates(){
        return array('Backbone/*',
                     'product_list', 
                     'product',
                     'product_data_row',
                     'product_edit',
                     'product_edit_data_row');
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
