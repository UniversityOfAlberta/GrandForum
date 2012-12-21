<?php

BackbonePage::register('Products', 'Products', 'grand-tools', dirname(__FILE__));

class Products extends BackbonePage {
    
    function getTemplates(){
        return array('product_list', 
                     'product_row', 
                     'product');
    }
    
    function getViews(){
        return array('ProductListView', 
                     'ProductView',
                     'CSVView',
                     'PersonLinkView',
                     'ProjectLinkView',
                     'ProductLinkView');
    }
    
    function getModels(){
        return array();
    }

}

?>
