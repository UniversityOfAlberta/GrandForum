<?php

BackbonePage::register('Products', 'Products', 'grand-tools', dirname(__FILE__));

class Products extends BackbonePage {
    
    function getTemplates(){
        return array('product_list', 'product_row');
    }
    
    function getViews(){
        return array('ProductListView');
    }
    
    function getModels(){
        return array();
    }

}

?>
