<?php

require_once("Tabs/PublicDiscTreeTab.php");
require_once("Tabs/PublicProjTreeTab.php");
require_once("Tabs/PublicUniTreeTab.php");
require_once("Tabs/PublicWordleTab.php");
require_once("Tabs/PublicChordTab.php");
require_once("Tabs/PublicPersonChordTab.php");
require_once("Tabs/PublicProjectClusterTab.php");
require_once("Tabs/PublicUniversityMapTab.php");

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['PublicVisualizations'] = 'PublicVisualizations'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['PublicVisualizations'] = $dir . 'PublicVisualizations.i18n.php';
$wgSpecialPageGroups['PublicVisualizations'] = 'network-tools';

$wgHooks['SubLevelTabs'][] = 'PublicVisualizations::createSubTabs';

function runPublicVisualizations($par) {
  PublicVisualizations::execute($par);
}

class PublicVisualizations extends SpecialPage{

	function PublicVisualizations() {
		SpecialPage::__construct("PublicVisualizations", '', true, 'runPublicVisualizations');
	}

    function execute(){
        global $wgOut, $config;
        $tabbedPage = new TabbedPage("publicVis");
        if($config->getValue('projectsEnabled')){
            $tabbedPage->addTab(new PublicChordTab());
            $tabbedPage->addTab(new PublicPersonChordTab());
            $tabbedPage->addTab(new PublicProjectClusterTab());
        }
        if($config->getValue('networkName') == "GRAND"){
            $tabbedPage->addTab(new PublicDiscTreeTab());
        }
        if($config->getValue('projectsEnabled')){
            $tabbedPage->addTab(new PublicProjTreeTab());
        }
        $tabbedPage->addTab(new PublicUniTreeTab());
        $tabbedPage->addTab(new PublicUniversityMapTab());
        if($config->getValue('projectsEnabled')){
            $tabbedPage->addTab(new PublicWordleTab());
        }
        $tabbedPage->showPage();
    }
    
    static function createSubTabs(&$tabs){
	    global $wgServer, $wgScriptPath, $wgTitle, $wgUser;
        $selected = @($wgTitle->getText() == "PublicVisualizations") ? "selected" : false;
        $tabs["Main"]['subtabs'][] = TabUtils::createSubTab("Visualizations", "$wgServer$wgScriptPath/index.php/Special:PublicVisualizations", $selected);
	    return true;
    }
}
?>
