<?php

BackbonePage::register('PharmacyMap', 'PharmacyMap', 'network-tools', dirname(__FILE__));

$wgHooks['TopLevelTabs'][] = 'PharmacyMap::createTab';
$wgHooks['SubLevelTabs'][] = 'PharmacyMap::createSubTabs';

class PharmacyMap extends BackbonePage {
    
    function isListed(){
        return false;
    }
    
    function userCanExecute($user){
        $me = Person::newFromWgUser();
        return $me->isLoggedIn();
    }
    
    static function getCategoryJSON(){
        $dir = dirname(__FILE__) . '/';
        $json = json_decode(file_get_contents("{$dir}categories.json"));
        return $json;
    }
    
    function getTemplates(){
        global $wgOut;
        $json = self::getCategoryJSON();
        $wgOut->addHTML("<script type='text/javascript'>
            var cat_json = ".json_encode($json).";
        </script>");
        return array('Backbone/*',
		     'pharmacy_map',
		     'pharmacy_add',
		     'community_row',
		     'category_buttons',
		    );
    }
    
    function getViews(){
        return array('Backbone/*',
		     'PharmacyMapView',
		     'PharmacyAddView',
		     'CommunityRowView',
		     'CategoryButtonsView',
		    );
    }
    
    function getModels(){
        return array('Backbone/*');
    }
    
    static function createTab(&$tabs){
        $tabs["Map"] = TabUtils::createTab("Community Program Library");
        return true;
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle;
        if($wgUser->isLoggedIn()){
            if(AVOIDDashboard::hasSubmittedSurvey()){
                $selected = @($wgTitle->getText() == "PharmacyMap") ? "selected" : false;
                $tabs["Map"]['subtabs'][] = TabUtils::createSubTab("Community Program Library", "{$wgServer}{$wgScriptPath}/index.php/Special:PharmacyMap", $selected);
            }
        }
        return true;
    }
    
    static function createToolboxLinks(&$toolbox){
        global $wgServer, $wgScriptPath, $wgUser,$wgOut,$wgLang;
        $me = Person::newFromWgUser();
        $title_locate = "Locate a Community";
        if($wgLang->getCode() == "fr"){
             $title_locate = "Localiser une Pharmacie";
        }
        $title_add = "Add a Community";
        if($wgLang->getCode() == "fr"){
             $title_add = "Ajouter une Pharmacie";
        }
        if($me->isLoggedIn()){
            if(AVOIDDashboard::hasSubmittedSurvey()){
                $toolbox['Other']['links'][] = TabUtils::createToolboxLink($title_locate, "$wgServer$wgScriptPath/index.php/Special:PharmacyMap");
            }
        }
        if($me->isRoleAtLeast(HQP)){
            if(AVOIDDashboard::hasSubmittedSurvey()){
                $toolbox['Other']['links'][] = TabUtils::createToolboxLink($title_add, "$wgServer$wgScriptPath/index.php/Special:PharmacyMap#/add");
            }
        }
        return true;
    }
}

?>