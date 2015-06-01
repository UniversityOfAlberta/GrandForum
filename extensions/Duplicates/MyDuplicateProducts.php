<?php

$dir = dirname(__FILE__) . '/';

$wgSpecialPages['MyDuplicateProducts'] = 'MyDuplicateProducts'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['MyDuplicateProducts'] = $dir . 'MyDuplicateProducts.i18n.php';
$wgSpecialPageGroups['MyDuplicateProducts'] = 'other-tools';

$wgHooks['ToolboxLinks'][] = 'MyDuplicateProducts::createToolboxLinks';

function runMyDuplicateProducts($par){
    MyDuplicateProducts::execute($par);
}

class MyDuplicateProducts extends SpecialPage{

	function MyDuplicateProducts() {
		SpecialPage::__construct("MyDuplicateProducts", HQP.'+', true, 'runMyDuplicateProducts');
	}

	function execute($par){
	    global $wgServer, $wgScriptPath, $wgOut, $wgUser, $config;
	    $me = Person::newFromId($wgUser->getId());
        $handlers = AbstractDuplicatesHandler::$handlers;
        $tabbedPage = new TabbedPage("duplicates");
        $tabbedPage->addTab(new DuplicatesTab("Publications", $handlers['myPublication']));
        $tabbedPage->addTab(new DuplicatesTab("Artifacts", $handlers['myArtifact']));
        $tabbedPage->addTab(new DuplicatesTab("Activities", $handlers['myActivity']));
        $tabbedPage->addTab(new DuplicatesTab("Press", $handlers['myPress']));
        $tabbedPage->addTab(new DuplicatesTab("Awards", $handlers['myAward']));
        $tabbedPage->addTab(new DuplicatesTab("Presentations", $handlers['myPresentation']));
        $wgOut->setPageTitle("My Duplicate ".Inflect::pluralize($config->getValue('productsTerm')));
        $tabbedPage->showPage();
	}
	
	static function createToolboxLinks(&$toolbox){
	    global $wgServer, $wgScriptPath;
	    $me = Person::newFromWgUser();
	    if($me->isRoleAtLeast(HQP)){
	        $toolbox['Products']['links'][] = TabUtils::createToolboxLink("Review Duplicates", "$wgServer$wgScriptPath/index.php/Special:MyDuplicateProducts");
	    }
	    return true;
	}
}

?>
