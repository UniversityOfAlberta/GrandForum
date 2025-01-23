<?php

require_once("DepartmentTab.php");
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
        global $wgOut, $wgServer, $wgScriptPath;
        
        $tabbedPage = new TabbedAjaxPage("qasummary");
        $person = Person::newFromWgUser();
        $person->getFecPersonalInfo();
        $departments = @array_keys($person->departments);
        $department = @$departments[0];
        if(self::checkRole($person) || $person->isSubRole("QA_PHYS")  || $department == "Physics")
            $tabbedPage->addTab(new DepartmentTab("Physics", array("PHYS")));
        if(self::checkRole($person) || $person->isSubRole("QA_CHEM")  || $department == "Chemistry")
            $tabbedPage->addTab(new DepartmentTab("Chemistry", array("CHEM")));
        if(self::checkRole($person) || $person->isSubRole("QA_BIOL")  || $department == "Biological Sciences")
            $tabbedPage->addTab(new DepartmentTab("Biological Sciences", array("BIOL")));
        if(self::checkRole($person) || $person->isSubRole("QA_CMPUT") || $department == "Computing Science")
            $tabbedPage->addTab(new DepartmentTab("Computing Science", array("CMPUT")));
        if(self::checkRole($person) || $person->isSubRole("QA_MATH")  || $department == "Mathematical And Statistical Sciences")
            $tabbedPage->addTab(new DepartmentTab("Mathematical And Statistical Sciences", array("MATH", "STAT")));
        if(self::checkRole($person) || $person->isSubRole("QA_EAS")   || $department == "Earth And Atmospheric Sciences")
            $tabbedPage->addTab(new DepartmentTab("Earth And Atmospheric Sciences", array("EAS")));
        if(self::checkRole($person) || $person->isSubRole("QA_PSYCH") || $department == "Psychology")
            $tabbedPage->addTab(new DepartmentTab("Psychology", array("PSYCH")));
        
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
                $me->isRole(CHAIR) ||
                $me->isSubRole('QA_PHYS') ||
                $me->isSubRole('QA_CHEM') ||
                $me->isSubRole('QA_BIOL') ||
                $me->isSubRole('QA_CMPUT') ||
                $me->isSubRole('QA_MATH') ||
                $me->isSubRole('QA_EAS') ||
                $me->isSubRole('QA_PSYCH'));
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgTitle, $wgUser;
        if(self::userCanExecute($wgUser)){
            $selected = ($wgTitle->getNSText() == "Special" && ($wgTitle->getText() == "QASummary")) ? "selected" : "";
            $tabs['Manager']['subtabs'][] = TabUtils::createSubTab("QA Summary", 
                                                                   "$wgServer$wgScriptPath/index.php/Special:QASummary", 
                                                                   "$selected");
        }
    }
    
}

?>
