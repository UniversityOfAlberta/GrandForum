<?php

require_once("Tabs/PublicChordTab.php");

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['PublicVisualizations'] = 'PublicVisualizations'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['PublicVisualizations'] = $dir . 'PublicVisualizations.i18n.php';
$wgSpecialPageGroups['PublicVisualizations'] = 'network-tools';

$wgHooks['SubLevelTabs'][] = 'PublicVisualizations::createSubTabs';

function runPublicVisualizations($par) {
  PublicVisualizations::run($par);
}

class PublicVisualizations extends SpecialPage{

	function PublicVisualizations() {
		wfLoadExtensionMessages('PublicVisualizations');
		SpecialPage::SpecialPage("PublicVisualizations", '', true, 'runPublicVisualizations');
	}

    function run(){
        global $wgOut;
        $tabbedPage = new TabbedPage("publicVis");
        $tabbedPage->addTab(new PublicChordTab());
        $tabbedPage->showPage();
    }
    
    static function createSubTabs($tabs){
	    global $wgServer, $wgScriptPath, $wgTitle, $wgUser;
        $selected = @($wgTitle->getText() == "PublicVisualizations") ? "selected" : false;
        $tabs["Main"]['subtabs'][] = TabUtils::createSubTab("Visualizations", "$wgServer$wgScriptPath/index.php/Special:PublicVisualizations", $selected);
	    return true;
    }
}
?>
