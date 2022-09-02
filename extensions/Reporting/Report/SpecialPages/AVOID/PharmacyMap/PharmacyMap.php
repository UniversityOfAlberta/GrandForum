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
    
    static function getCategoryLeaves($categoryJSON=null){
        $categories = array();
        if($categoryJSON == null){
            $categoryJSON = PharmacyMap::getCategoryJSON();
            foreach($categoryJSON as $category){
                $categories = array_merge($categories, PharmacyMap::getCategoryLeaves($category));
            }
        }
        else {
            if(isset($categoryJSON->children)){
                // Category has children
                foreach($categoryJSON->children as $category){
                    $categories = array_merge($categories, PharmacyMap::getCategoryLeaves($category));
                }
            }
            else{
                // Leaf found
                $categories[] = $categoryJSON;
            }
        }
        return $categories;
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
        $tabs["Map"] = TabUtils::createTab("Community Programs", "", "");
        return true;
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle;
        if($wgUser->isLoggedIn()){
            if(AVOIDDashboard::checkAllSubmissions($wgUser->getId())){
                $selected = @($wgTitle->getText() == "PharmacyMap") ? "selected" : false;
                $tabs["Map"]['subtabs'][] = TabUtils::createSubTab("Community Programs", "{$wgServer}{$wgScriptPath}/index.php/Special:PharmacyMap", $selected);
            }
        }
        return true;
    }

}

?>
