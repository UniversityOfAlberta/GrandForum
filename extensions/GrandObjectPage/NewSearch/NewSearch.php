<?php

BackbonePage::register('NewSearch', 'NewSearch', 'grand-tools', dirname(__FILE__));

class NewSearch extends BackbonePage {
    
    function getTemplates(){
        return array('search');
    }
    
    function getViews(){
        return array('SearchView');
    }
    
    function getModels(){
        return array('NewSearch');
    }

}

?>