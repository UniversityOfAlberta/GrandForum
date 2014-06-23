<?php

require_once("Tabs/AdminChordTab.php");
require_once("Tabs/AdminProjectFundingTab.php");
require_once("Tabs/AdminUniversityFundingTab.php");
require_once("Tabs/AdminMapTab.php");
require_once("Tabs/AdminCustomTab.php");
require_once("Tabs/AdminUniTreeTab.php");
require_once("Tabs/AdminDiscTreeTab.php");
require_once("Tabs/AdminProjTreeTab.php");
require_once("Tabs/AdminUniversityMapTab.php");
require_once("Tabs/AdminProjectClusterTab.php");

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['AdminVisualizations'] = 'AdminVisualizations'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['AdminVisualizations'] = $dir . 'AdminVisualizations.i18n.php';
$wgSpecialPageGroups['AdminVisualizations'] = 'other-tools';

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
        $tabbedPage->addTab(new AdminProjectClusterTab());
        //$tabbedPage->addTab(new AdminProjectFundingTab());
        //$tabbedPage->addTab(new AdminUniversityFundingTab());
        //$tabbedPage->addTab(new AdminMapTab());
        //$tabbedPage->addTab(new AdminCustomTab());
        $tabbedPage->showPage();
    }
}
?>
