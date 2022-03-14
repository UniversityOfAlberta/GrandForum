<?php

require_once("DepartmentTab.php");
require_once("QACVGenerator.php");

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['QASummary'] = 'QASummary'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['QASummary'] = $dir . 'QASummary.i18n.php';
$wgSpecialPageGroups['QASummary'] = 'network-tools';

$wgHooks['SubLevelTabs'][] = 'QASummary::createSubTabs';

class QASummary extends SpecialPage{

    function QASummary() {
        parent::__construct("QASummary", null, true);
    }

    function execute($par){
        global $wgOut, $wgServer, $wgScriptPath;
        
        $tabbedPage = new TabbedAjaxPage("qasummary");
        $person = Person::newFromWgUser();
        
        if($person->isRoleAtLeast(STAFF) || $person->isSubRole("QA_PHYS"))
            $tabbedPage->addTab(new DepartmentTab("Physics", array("PHYS")));
        if($person->isRoleAtLeast(STAFF) || $person->isSubRole("QA_CHEM"))
            $tabbedPage->addTab(new DepartmentTab("Chemistry", array("CHEM")));
        if($person->isRoleAtLeast(STAFF) || $person->isSubRole("QA_BIOL"))
            $tabbedPage->addTab(new DepartmentTab("Biological Sciences", array("BIOL")));
        if($person->isRoleAtLeast(STAFF) || $person->isSubRole("QA_CMPUT"))
            $tabbedPage->addTab(new DepartmentTab("Computing Science", array("CMPUT")));
        if($person->isRoleAtLeast(STAFF) || $person->isSubRole("QA_MATH"))
            $tabbedPage->addTab(new DepartmentTab("Mathematical And Statistical Sciences", array("MATH", "STAT")));
        if($person->isRoleAtLeast(STAFF) || $person->isSubRole("QA_EAS"))
            $tabbedPage->addTab(new DepartmentTab("Earth And Atmospheric Sciences", array("EAS")));
        if($person->isRoleAtLeast(STAFF) || $person->isSubRole("QA_PSYCH"))
            $tabbedPage->addTab(new DepartmentTab("Psychology", array("PSYCH")));
        
        $tabbedPage->showPage();
        
        $wgOut->addHTML("<script type='text/javascript'>
            $('.custom-title').hide();
        </script>");
    }
    
    function userCanExecute($user){
        $me = Person::newFromUser($user);
        return ($me->isRole(CHAIR) || 
                $me->isRole(DEAN) || 
                $me->isRole(DEANEA) || 
                $me->isRole(VDEAN) || 
                $me->isRoleAtLeast(STAFF) || 
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
