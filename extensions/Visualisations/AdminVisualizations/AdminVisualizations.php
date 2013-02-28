<?php

require_once("Tabs/AdminChordTab.php");
require_once("Tabs/AdminProjectFundingTab.php");
require_once("Tabs/AdminUniversityFundingTab.php");
require_once("Tabs/AdminMapTab.php");

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
        $tabbedPage->addTab(new AdminProjectFundingTab());
        $tabbedPage->addTab(new AdminUniversityFundingTab());
        $tabbedPage->addTab(new AdminMapTab());
        $tabbedPage->showPage();
    }
}
?>
