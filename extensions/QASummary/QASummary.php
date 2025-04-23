<?php

autoload_register('QASummary');

require_once("QACVGenerator.php");

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['QASummary'] = 'QASummary'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['QASummary'] = $dir . 'QASummary.i18n.php';
$wgSpecialPageGroups['QASummary'] = 'network-tools';

$wgHooks['SubLevelTabs'][] = 'QASummary::createSubTabs';

class QASummary extends SpecialPage{

    function __construct() {
        parent::__construct("QASummary", null, true);
    }

    function execute($par){
        global $wgOut, $wgServer, $wgScriptPath, $facultyMapSimple;
        $tabbedPage = new TabbedAjaxPage("qasummary");
        $person = Person::newFromWgUser();
        
        $departments = $facultyMapSimple[getFaculty()];
        foreach($departments as $key => $department){
            $tabbedPage->addTab(new DepartmentTab($department, array($key)));
        }
        $tabbedPage->showPage();
        
        $wgOut->addHTML("<script type='text/javascript'>
            $('.custom-title').hide();
        </script>");
    }
    
    function checkRole($person){
        return ($person->isRole(DEAN) || 
                $person->isRole(DEANEA) || 
                $person->isRole(VDEAN) || 
                $person->isRoleAtLeast(STAFF));
    }
    
    function userCanExecute($user){
        $me = Person::newFromUser($user);
        return (self::checkRole($me) || 
                $me->isRole(CHAIR));
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgTitle, $wgUser;
        if((new self)->userCanExecute($wgUser)){
            $selected = ($wgTitle->getNSText() == "Special" && ($wgTitle->getText() == "QASummary")) ? "selected" : "";
            $tabs['Manager']['subtabs'][] = TabUtils::createSubTab("QA Summary", 
                                                                   "$wgServer$wgScriptPath/index.php/Special:QASummary", 
                                                                   "$selected");
        }
    }
    
}

?>
