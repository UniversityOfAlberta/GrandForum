<?php

BackbonePage::register('AdvancedSearch', 'Expert Search', 'network-tools', dirname(__FILE__));

class AdvancedSearch extends BackbonePage {
    function userCanExecute($user){
        return $user->isLoggedIn();
    }
    
    function getTemplates(){
        return array('search','Backbone/*');
    }
    
    function getViews(){
        return array('SearchView', 'SearchResultsView', 'PersonCardView');
    }
    
    function getModels(){
        return array('SearchResults','Backbone/*');
    }

}

?>
