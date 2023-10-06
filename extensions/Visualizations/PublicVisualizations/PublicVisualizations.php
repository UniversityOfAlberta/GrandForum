<?php

require_once("Tabs/PublicProjTreeTab.php");
require_once("Tabs/PublicUniTreeTab.php");
require_once("Tabs/PublicWordleTab.php");
require_once("Tabs/PublicChordTab.php");
require_once("Tabs/PublicPersonChordTab.php");
require_once("Tabs/PublicNetworkTab.php");
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

	function __construct() {
		SpecialPage::__construct("PublicVisualizations", '', false, 'runPublicVisualizations');
	}
	
	function userCanExecute($user){
	    global $config;
	    if($config->getValue('guestLockdown')){
	        return $user->isLoggedIn();
	    }
        return true;
    }

    function execute($par){
        global $wgOut, $config;
        $this->getOutput()->setPageTitle("Public Visualizations");
        $me = Person::newFromWgUser();
        $tabbedPage = new TabbedPage("publicVis");
        if($config->getValue('projectsEnabled')){
            $tabbedPage->addTab(new PublicChordTab());
            if($config->getValue('networkType') == "CFREF"){
                $tabbedPage->addTab(new PublicPersonChordTab());
            }
            $tabbedPage->addTab(new PublicProjectClusterTab());
            $tabbedPage->addTab(new PublicNetworkTab());
            $tabbedPage->addTab(new PublicProjTreeTab());
        }
        $tabbedPage->addTab(new PublicUniTreeTab());
        if($config->getValue('googleAPI') != ""){
            $tabbedPage->addTab(new PublicUniversityMapTab());
        }
        if($config->getValue('projectsEnabled')){
            $tabbedPage->addTab(new PublicWordleTab());
        }
        $tabbedPage->showPage();
        $wgOut->addHTML("<script type='text/javascript'>
            $('.custom-title').hide();
        </script>");
    }
    
    static function createSubTabs(&$tabs){
	    global $wgServer, $wgScriptPath, $wgTitle, $wgUser;
	    if((new self)->userCanExecute($wgUser)){
	        $selected = @($wgTitle->getText() == "PublicVisualizations") ? "selected" : false;
            $tabs["Main"]['subtabs'][] = TabUtils::createSubTab("Visualizations", "$wgServer$wgScriptPath/index.php/Special:PublicVisualizations", $selected);
	    }
	    return true;
    }
}
?>
