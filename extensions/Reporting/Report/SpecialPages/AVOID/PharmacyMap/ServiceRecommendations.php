<?php

BackbonePage::register('ServiceRecommendations', 'ServiceRecommendations', 'network-tools', dirname(__FILE__));

$wgHooks['TopLevelTabs'][] = 'ServiceRecommendations::createTab';
$wgHooks['SubLevelTabs'][] = 'ServiceRecommendations::createSubTabs';

class ServiceRecommendations extends PharmacyMap {
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle;
        if($wgUser->isLoggedIn()){
            if(AVOIDDashboard::checkAllSubmissions($wgUser->getId())){
                $selected = @($wgTitle->getText() == "ServiceRecommendations") ? "selected" : false;
                $tabs["Map"]['subtabs'] = array(TabUtils::createSubTab("Community Programs", "{$wgServer}{$wgScriptPath}/index.php/Special:ServiceRecommendations", $selected));
            }
        }
        return true;
    }

}

?>
