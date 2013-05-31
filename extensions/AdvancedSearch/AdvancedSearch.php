<?php

BackbonePage::register('AdvancedSearch', 'Expert Search', 'grand-tools', dirname(__FILE__));

class AdvancedSearch extends BackbonePage {
    
    function getTemplates(){
        return array('search','Backbone/*');
    }
    
    function getViews(){
        return array('SearchView', 'SearchResultsView', 'PersonCardView');
    }
    
    function getModels(){
        return array('AdvancedSearch','Backbone/*');
    }

}

?>