<?php

require_once("AbstractDuplicatesHandler.php");
require_once("DuplicatesTab.php");
require_once("Handlers/ProductHandler.php");
require_once("Handlers/MyProductHandler.php");
require_once("Handlers/PersonHandler.php");
require_once("Handlers/HQPHandler.php");
require_once("MyDuplicateProducts.php");

$dir = dirname(__FILE__) . '/';

$wgSpecialPages['Duplicates'] = 'Duplicates'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['Duplicates'] = $dir . 'Duplicates.i18n.php';
$wgSpecialPageGroups['Duplicates'] = 'other-tools';

UnknownAction::createAction('handleDuplicates');
$wgHooks['SubLevelTabs'][] = 'Duplicates::createSubTabs';

function handleDuplicates($action, $request){
    global $wgServer, $wgScriptPath;
    if($action == 'getDuplicates' ||
       $action == 'deleteDuplicates' ||
       $action == 'mergeDuplicates' ||
       $action == 'ignoreDuplicates'){
        require_once("Classes/simplediff/simplediff.php");
        ProductHandler::init();
	    MyProductHandler::init();
	    PersonHandler::init();
	    HQPHandler::init();
        foreach(AbstractDuplicatesHandler::$handlers as $handler){
            if($_GET['handler'] == $handler->id){
                if($action == "getDuplicates"){
                    session_write_close();
                    $handler->handleGet();
                    close();
                }
                else if($action == "deleteDuplicates"){
                    $handler->handleDelete();
                    DBFunctions::commit();
                    close();
                }
                else if($action == "mergeDuplicates"){
                    $handler->handleMerge();
                    DBFunctions::commit();
                    close();
                }
                else if($action == "ignoreDuplicates"){
                    $handler->handleIgnore();
                    DBFunctions::commit();
                    close();
                }
                break;
            }
        }
    }
    return true;
}

class Duplicates extends SpecialPage{

	function __construct() {
		SpecialPage::__construct("Duplicates", MANAGER.'+', true, 'Duplicates::execute');
	}
	
	function userCanExecute($user){
	    $me = Person::newFromUser($user);
	    if($me->isRoleAtLeast(MANAGER)){
	        return true;
	    }
	    else{
	        return false;
	    }
	}

	function execute($par){
	    global $wgServer, $wgScriptPath, $wgOut;
	    ProductHandler::init();
	    MyProductHandler::init();
	    PersonHandler::init();
        $handlers = AbstractDuplicatesHandler::$handlers;
        $tabbedPage = new TabbedPage("duplicates");
        $structure = Product::structure();
        foreach($structure['categories'] as $key => $cat){
            $key = str_replace("/", "", str_replace("-", "", str_replace(" ", "", $key)));
            $tabbedPage->addTab(new DuplicatesTab(Inflect::pluralize($key), $handlers[strtolower($key)]));
        }
        $tabbedPage->addTab(new DuplicatesTab("People", $handlers['people']));
        $tabbedPage->showPage();
	}
	
	static function createSubTabs(&$tabs){
	    global $wgServer, $wgScriptPath, $wgTitle, $wgUser;
	    $person = Person::newFromWgUser($wgUser);
	    if($person->isRoleAtLeast(MANAGER)){
	        $selected = @($wgTitle->getText() == "Duplicates") ? "selected" : false;
	        $tabs["Manager"]['subtabs'][] = TabUtils::createSubTab("Duplicates", "$wgServer$wgScriptPath/index.php/Special:Duplicates", $selected);
	    }
	    return true;
    }
}

?>
