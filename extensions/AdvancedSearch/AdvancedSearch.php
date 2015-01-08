<?php

BackbonePage::register('AdvancedSearch', 'Expert Search', 'network-tools', dirname(__FILE__));

$wgHooks['ToolboxLinks'][] = 'AdvancedSearch::createToolboxLinks';

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

    static function createToolboxLinks(&$toolbox){
        global $wgServer, $wgScriptPath;
        $me = Person::newFromWgUser();
        if(self::userCanExecute($me)){
            $toolbox['Other']['links'][] = TabUtils::createToolboxLink("Search for Experts", "$wgServer$wgScriptPath/index.php/Special:AdvancedSearch");
        }
        return true;
    }

}

?>
