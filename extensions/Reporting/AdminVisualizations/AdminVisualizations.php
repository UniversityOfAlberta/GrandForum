<?php

require_once("Tabs/AdminChordTab.php");
require_once("Tabs/AdminMapTab.php");
require_once("Tabs/AdminUniTreeTab.php");
require_once("Tabs/AdminDiscTreeTab.php");
require_once("Tabs/AdminProjTreeTab.php");
require_once("Tabs/AdminUniversityMapTab.php");
require_once("Tabs/HQPPromotionsTab.php");

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['AdminVisualizations'] = 'AdminVisualizations'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['AdminVisualizations'] = $dir . 'AdminVisualizations.i18n.php';
$wgSpecialPageGroups['AdminVisualizations'] = 'other-tools';

$wgHooks['SubLevelTabs'][] = 'AdminVisualizations::createSubTabs';

function runAdminVisualizations($par) {
  AdminVisualizations::run($par);
}

class AdminVisualizations extends SpecialPage{

	function AdminVisualizations() {
		wfLoadExtensionMessages('AdminVisualizations');
		SpecialPage::SpecialPage("AdminVisualizations", MANAGER.'+', true, 'runAdminVisualizations');
	}

    function run(){
        global $wgOut;
        $tabbedPage = new TabbedPage("adminVis");
        $tabbedPage->addTab(new AdminChordTab());
        $tabbedPage->addTab(new AdminUniTreeTab());
        $tabbedPage->addTab(new AdminDiscTreeTab());
        $tabbedPage->addTab(new AdminProjTreeTab());
        $tabbedPage->addTab(new AdminUniversityMapTab());
        $tabbedPage->addTab(new HQPPromotionsTab());
        $tabbedPage->showPage();
    }
    
    static function createSubTabs($tabs){
	    global $wgServer, $wgScriptPath, $wgTitle, $wgUser;
	    $person = Person::newFromWgUser($wgUser);
	    if($person->isRoleAtLeast(MANAGER)){
	        $selected = @($wgTitle->getText() == "AdminVisualizations") ? "selected" : false;
	        $tabs["Manager"]['subtabs'][] = TabUtils::createSubTab("Visualizations", "$wgServer$wgScriptPath/index.php/Special:AdminVisualizations", $selected);
	    }
	    return true;
    }
}
?>
