<?php

$wgHooks['SubLevelTabs'][] = 'createRebootSubTabs';

function createRebootSubTabs(&$tabs){
    global $wgServer, $wgScriptPath, $wgUser, $config, $wgTitle;
    if($wgUser->isLoggedIn()){
        $selected = ($wgTitle->getNsText() == "Reboot" && $wgTitle->getText() == "Main") ? "selected" : "";
        $subTab = TabUtils::createSubTab("Reboot");
        $subTab['dropdown'][] = TabUtils::createSubTab("Overview", "$wgServer$wgScriptPath/index.php/Reboot:Main", "$selected");
        $tabs['Main']['subtabs'][] = $subTab;
    }
    return true;
}



?>
