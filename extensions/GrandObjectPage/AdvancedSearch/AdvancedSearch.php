<?php

BackbonePage::register('AdvancedSearch', 'AdvancedSearch', 'grand-tools', dirname(__FILE__));

class AdvancedSearch extends BackbonePage {
    
    function getTemplates(){
        return array('search','person_card');
    }
    
    function getViews(){
        return array('SearchView');
    }
    
    function getModels(){
        return array('AdvancedSearch','Backbone/*');
    }

}

?>