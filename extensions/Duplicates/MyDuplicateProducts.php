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
	    ProductHandler::init();
	    MyProductHandler::init();
	    PersonHandler::init();
        $handlers = AbstractDuplicatesHandler::$handlers;
        $tabbedPage = new TabbedPage("duplicates");
        
        $structure = Product::structure();
        foreach($structure['categories'] as $key => $cat){
            $key = str_replace(" ", "", $key);
            $tabbedPage->addTab(new DuplicatesTab(Inflect::pluralize($key), $handlers["my$key"]));
        }
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
