<?php

autoload_register('Reporting/AdminVisualizations/Tabs');

UnknownAction::createAction('AdminChordTab::getAdminChordData');
UnknownAction::createAction('AdminUniTreeTab::getAdminUniTreeData');
UnknownAction::createAction('AdminProjTreeTab::getAdminProjTreeData');
UnknownAction::createAction('AdminUniversityMapTab::getAdminUniversityMapData');
UnknownAction::createAction('HQPPromotionsTab::getHQPPromotionsData');

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['AdminVisualizations'] = 'AdminVisualizations'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['AdminVisualizations'] = $dir . 'AdminVisualizations.i18n.php';
$wgSpecialPageGroups['AdminVisualizations'] = 'other-tools';

$wgHooks['SubLevelTabs'][] = 'AdminVisualizations::createSubTabs';

function runAdminVisualizations($par) {
  AdminVisualizations::execute($par);
}

class AdminVisualizations extends SpecialPage{

	function __construct() {
		SpecialPage::__construct("AdminVisualizations", MANAGER.'+', true, 'runAdminVisualizations');
	}

    function execute($par){
        global $wgOut;
        $this->getOutput()->setPageTitle("Admin Visualizations");
        $tabbedPage = new TabbedPage("adminVis");
        $tabbedPage->addTab(new AdminChordTab());
        $tabbedPage->addTab(new AdminUniTreeTab());
        $tabbedPage->addTab(new AdminProjTreeTab());
        $tabbedPage->addTab(new AdminUniversityMapTab());
        $tabbedPage->addTab(new HQPPromotionsTab());
        $tabbedPage->showPage();
    }
    
    static function createSubTabs(&$tabs){
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
