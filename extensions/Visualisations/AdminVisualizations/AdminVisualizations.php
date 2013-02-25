<?php

require_once("Tabs/AdminChordTab.php");
require_once("Tabs/AdminHighChartTab.php");
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
        $tabbedPage->addTab(new AdminHighChartTab());
        $tabbedPage->addTab(new AdminMapTab());
        $tabbedPage->showPage();
    }
    
    function addTabs($skin, &$content_actions){
        global $wgUser, $wgServer, $wgScriptPath, $wgTitle;
        $me = Person::newFromId($wgUser->getId());
        if($me->isRole($wgTitle->getNSText()) && $me->getName() == $wgTitle->getText()){
            $content_actions = array();
            $content_actions[] = array('text' => $me->getNameForForms(),
                                       'class' => 'selected',
                                       'href' => "$wgServer$wgScriptPath/index.php/{$wgTitle->getNSText()}:{$wgTitle->getText()}"
                                    
            );
        }
        return true;
    }
}
?>
