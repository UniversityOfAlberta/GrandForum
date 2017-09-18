<?php

require_once("DepartmentTab.php");

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['QASummary'] = 'QASummary'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['QASummary'] = $dir . 'QASummary.i18n.php';
$wgSpecialPageGroups['QASummary'] = 'network-tools';

$wgHooks['SubLevelTabs'][] = 'QASummary::createSubTabs';

class QASummary extends SpecialPage{

    function QASummary() {
        parent::__construct("QASummary", STAFF.'+', true);
    }

    function execute($par){
        global $wgOut, $wgServer, $wgScriptPath;
        $tabbedPage = new TabbedPage("qasummary");
        
        $tabbedPage->addTab(new DepartmentTab("Physics", array("PHYS")));
        $tabbedPage->addTab(new DepartmentTab("Chemistry", array("CHEM")));
        $tabbedPage->addTab(new DepartmentTab("Biological Sciences", array("BIOL")));
        $tabbedPage->addTab(new DepartmentTab("Computing Science", array("CMPUT")));
        $tabbedPage->addTab(new DepartmentTab("Mathematical And Statistical Sciences", array("MATH", "STAT")));
        $tabbedPage->addTab(new DepartmentTab("Earth And Atmospheric Sciences", array("EAS")));
        
        $tabbedPage->showPage();
        
        $wgOut->addHTML("<script type='text/javascript'>
            $('.custom-title').hide();
        </script>");
    }
    
    function userCanExecute($user){
        $person = Person::newFromUser($user);
        return $person->isRoleAtLeast(STAFF);
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
