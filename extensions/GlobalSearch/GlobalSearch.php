<?php

$wgHooks['BeforePageDisplay'][] = 'initGlobalSearch';

function initGlobalSearch($out, $skin){
    global $wgServer, $wgScriptPath, $config;
    $me = Person::newFromWgUser();
    if($config->getValue('guestLockdown') && !$me->isLoggedIn()){
        return true;
    }
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
        return array('Backbone/*',
                     'global_search',
                     'global_search_results',
                     'global_search_group');
    }
    
    function getViews(){
        return array('Backbone/*',
                     'GlobalSearchView');
    }
    
    function getModels(){
        return array('GlobalSearch');
    } 
    
}

?>
