<?php

require_once("UserSearch.php");

$wgHooks['BeforePageDisplay'][] = 'initGlobalSearch';

function initGlobalSearch($out, $skin){
    global $wgServer, $wgScriptPath;
    BackbonePage::$dirs['globalsearch'] = dirname(__FILE__);
    $globalSearch = new GlobalSearch();
    $globalSearch->loadTemplates();
    $globalSearch->loadModels();
    $globalSearch->loadHelpers();
    $globalSearch->loadViews();
    $globalSearch->loadMain();
    return true;
}


class GlobalSearch extends BackbonePage {
    
    function getTemplates(){
        return array('Backbone/small_person_card',
                     'Backbone/small_wiki_card',
                     'global_search',
                     'global_search_results',
                     'global_search_group');
    }
    
    function getViews(){
        return array('Backbone/SmallPersonCardView',
                     'Backbone/SmallWikiCardView',
                     'GlobalSearchView');
    }
    
    function getModels(){
        return array('GlobalSearch');
    } 
    
}

?>
