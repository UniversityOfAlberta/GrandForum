<?php

require_once("Classes/simplediff/simplediff.php");
require_once("AbstractDuplicatesHandler.php");
require_once("DuplicatesTab.php");
require_once("Handlers/ProductHandler.php");
require_once("Handlers/PersonHandler.php");

$dir = dirname(__FILE__) . '/';

$wgSpecialPages['Duplicates'] = 'Duplicates'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['Duplicates'] = $dir . 'Duplicates.i18n.php';
$wgSpecialPageGroups['Duplicates'] = 'other-tools';

$wgHooks['UnknownAction'][] = 'handleDuplicates';

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

function runDuplicates($par){
    Duplicates::run($par);
}

class Duplicates extends SpecialPage{

	function Duplicates() {
		wfLoadExtensionMessages('Duplicates');
		SpecialPage::SpecialPage("Duplicates", STAFF.'+', true, 'runDuplicates');
	}

	function run($par){
	    global $wgServer, $wgScriptPath, $wgOut, $wgUser;
	    $me = Person::newFromId($wgUser->getId());
        if($me->getName() == "Adrian.Sheppard" || $me->getName() == "Admin"){
            $handlers = AbstractDuplicatesHandler::$handlers;
            $tabbedPage = new TabbedPage("duplicates");
            $tabbedPage->addTab(new DuplicatesTab("Publications", $handlers['publication']));
            $tabbedPage->addTab(new DuplicatesTab("Artifacts", $handlers['artifact']));
            $tabbedPage->addTab(new DuplicatesTab("Activities", $handlers['activity']));
            $tabbedPage->addTab(new DuplicatesTab("Press", $handlers['press']));
            $tabbedPage->addTab(new DuplicatesTab("Awards", $handlers['award']));
            $tabbedPage->addTab(new DuplicatesTab("People", $handlers['people']));
            $tabbedPage->showPage();
        }
        else {
            $wgOut->setPageTitle("Permission error");
            $wgOut->addHTML("You are not allowed to execute the action you have requested.");
        }
	}
}

?>
