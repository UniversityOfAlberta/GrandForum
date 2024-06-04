<?php

require_once("QACVDepartmentTab.php");

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['QACVGenerator'] = 'QACVGenerator'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['QACVGenerator'] = $dir . 'QACVGenerator.i18n.php';
$wgSpecialPageGroups['QACVGenerator'] = 'network-tools';

$wgHooks['SubLevelTabs'][] = 'QACVGenerator::createSubTabs';

class QACVGenerator extends SpecialPage{

    function QACVGenerator() {
        parent::__construct("QACVGenerator", STAFF.'+', true);
    }

    function execute($par){
        global $wgOut, $wgServer, $wgScriptPath;
        $tabbedPage = new TabbedAjaxPage("qacv");
        $person = Person::newFromWgUser();
        
        if($person->isRoleAtLeast(STAFF) || $person->isSubRole("QA_PHYS"))
            $tabbedPage->addTab(new QACVDepartmentTab("Physics", array("PHYS")));
        if($person->isRoleAtLeast(STAFF) || $person->isSubRole("QA_CHEM"))
            $tabbedPage->addTab(new QACVDepartmentTab("Chemistry", array("CHEM")));
        if($person->isRoleAtLeast(STAFF) || $person->isSubRole("QA_BIOL"))
            $tabbedPage->addTab(new QACVDepartmentTab("Biological Sciences", array("BIOL")));
        if($person->isRoleAtLeast(STAFF) || $person->isSubRole("QA_CMPUT"))
            $tabbedPage->addTab(new QACVDepartmentTab("Computing Science", array("CMPUT")));
        if($person->isRoleAtLeast(STAFF) || $person->isSubRole("QA_MATH"))
            $tabbedPage->addTab(new QACVDepartmentTab("Mathematical And Statistical Sciences", array("MATH", "STAT")));
        if($person->isRoleAtLeast(STAFF) || $person->isSubRole("QA_EAS"))
            $tabbedPage->addTab(new QACVDepartmentTab("Earth And Atmospheric Sciences", array("EAS")));
        if($person->isRoleAtLeast(STAFF) || $person->isSubRole("QA_EAS"))
            $tabbedPage->addTab(new QACVDepartmentTab("Psychology", array("PSYCH")));
        
        $tabbedPage->showPage();
        
        $wgOut->addHTML("<script type='text/javascript'>
            $('.custom-title').hide();
        </script>");
    }
    
    function userCanExecute($user){
        $person = Person::newFromUser($user);
        return ($person->isRoleAtLeast(STAFF) || $person->isSubRole('QA_PHYS') ||
                                                 $person->isSubRole('QA_CHEM') ||
                                                 $person->isSubRole('QA_BIOL') ||
                                                 $person->isSubRole('QA_CMPUT') ||
                                                 $person->isSubRole('QA_MATH') ||
                                                 $person->isSubRole('QA_EAS') ||
                                                 $person->isSubRole('QA_PSYCH'));
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgTitle, $wgUser;
        /*if(self::userCanExecute($wgUser)){
            $selected = ($wgTitle->getNSText() == "Special" && ($wgTitle->getText() == "QACVGenerator")) ? "selected" : "";
            $tabs['Manager']['subtabs'][] = TabUtils::createSubTab("QACV Generator", 
                                                                   "$wgServer$wgScriptPath/index.php/Special:QACVGenerator", 
                                                                   "$selected");
        }*/
    }
    
}

?>
