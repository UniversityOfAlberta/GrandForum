<?php

require_once("Classes/simplediff/simplediff.php");
require_once("AbstractDuplicatesHandler.php");
require_once("DuplicatesTab.php");
require_once("Handlers/ProductHandler.php");
require_once("Handlers/MyProductHandler.php");
require_once("Handlers/PersonHandler.php");
require_once("MyDuplicateProducts.php");

$dir = dirname(__FILE__) . '/';

$wgSpecialPages['Duplicates'] = 'Duplicates'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['Duplicates'] = $dir . 'Duplicates.i18n.php';
$wgSpecialPageGroups['Duplicates'] = 'other-tools';

$wgHooks['SubLevelTabs'][] = 'Duplicates::createSubTabs';

UnknownAction::createAction('handleDuplicates');

function handleDuplicates($action, $request){
    global $wgServer, $wgScriptPath;
    if($action == 'getDuplicates' ||
       $action == 'deleteDuplicates' ||
       $action == 'ignoreDuplicates'){
        foreach(AbstractDuplicatesHandler::$handlers as $handler){
            if($_GET['handler'] == $handler->id){
                if($action == "getDuplicates"){
                    session_write_close();
                    $handler->handleGet();
                    exit;
                }
                else if($action == "deleteDuplicates"){
                    $handler->handleDelete();
                    exit;
                }
                else if($action == "ignoreDuplicates"){
                    $handler->handleIgnore();
                    exit;
                }
                break;
            }
        }
    }
    return true;
}

class Duplicates extends SpecialPage{

	function Duplicates() {
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
        $handlers = AbstractDuplicatesHandler::$handlers;
        $tabbedPage = new TabbedPage("duplicates");
        $structure = Product::structure();
        foreach($structure['categories'] as $key => $cat){
            $key = str_replace(" ", "", $key);
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
