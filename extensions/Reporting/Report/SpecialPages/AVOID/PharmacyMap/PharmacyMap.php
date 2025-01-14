<?php

BackbonePage::register('PharmacyMap', 'PharmacyMap', 'network-tools', dirname(__FILE__));

$wgHooks['TopLevelTabs'][] = 'PharmacyMap::createTab';
$wgHooks['SubLevelTabs'][] = 'PharmacyMap::createSubTabs';

class PharmacyMap extends BackbonePage {
    
    function isListed(){
        return false;
    }
    
    function userCanExecute($user){
        global $config;
        $me = Person::newFromUser($user);
        if($config->getValue('networkFullName') == "AVOID Australia" &&
            !($me->isRoleAtLeast(STAFF) ||
              $me->isRole("GroupA") && !$me->isRoleOn("GroupA", date('Y-m-d', time() - 86400*30.5*6)) || // Allow A until 6 months
              $me->isRole("GroupD") && !$me->isRoleOn("GroupD", date('Y-m-d', time() - 86400*30.5*6)) // Allow D until 6 months
            )){
            return false;
        }
        return true;
    }
    
    function execute($par){
        parent::execute($par);
        $me = Person::newFromWgUser();
        if(!$me->isLoggedIn()){
            $ip = (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
            $dc = DataCollection::newFromUserId(0, "PharmacyMap/{$ip}");
            $dc->page = "PharmacyMap/{$ip}";
            $dc->userId = 0;
            $dc->allowed = true;
            $date = date('Y-m-d');
            $dc->setField($date, $dc->getField($date, 0)+1);
            if($dc->exists()){
                $dc->update();
            }
            else{
                $dc->create();
            }
        }
    }
    
    static function getCategoryJSON(){
        global $config;
        $dir = dirname(__FILE__) . '/';
        if($config->getValue('networkFullName') == "AVOID Australia"){
            $json = json_decode(file_get_contents("{$dir}categories_australia.json"));
        }
        else{
            $json = json_decode(file_get_contents("{$dir}categories.json"));
        }
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
                unset($categoryJSON->children);
                $categories[] = $categoryJSON;
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
        $me = Person::newFromWgUser();
        if(isset($_GET['clickedProgram'])){
            $dcs = DataCollection::allFromUserId($me->getId(), "ProgramLibrary-*");
            $clicks = 0;
            foreach($dcs as $dc){
                $clicks += $dc->getField('websiteClicks', 0);
            }
            if($clicks >= 5){
                Gamification::log("5CommunitySupports");
            }
            exit;
        }
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
        $tabs["Map"] = TabUtils::createTab("<en>Community Programs</en><fr>Répertoire des ressources</fr>", "", "");
        return true;
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle;
        if($wgUser->isLoggedIn()){
            if(AVOIDDashboard::checkAllSubmissions($wgUser->getId()) && (new self())->userCanExecute($wgUser)){
                $selected = @($wgTitle->getText() == "PharmacyMap") ? "selected" : false;
                $tabs["Map"]['subtabs'][] = TabUtils::createSubTab("Community Programs", "{$wgServer}{$wgScriptPath}/index.php/Special:PharmacyMap", $selected);
            }
        }
        return true;
    }

}

?>
