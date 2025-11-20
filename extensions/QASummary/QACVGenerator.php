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
        foreach($facultyMapSimple[getFaculty()] as $key => $dept){
            if($person->isRoleAtLeast(STAFF))
                $tabbedPage->addTab(new QACVDepartmentTab($dept, array($key)));
        }        
        $tabbedPage->showPage();
        
        $wgOut->addHTML("<script type='text/javascript'>
            $('.custom-title').hide();
        </script>");
    }
    
    function userCanExecute($user){
        $person = Person::newFromUser($user);
        return ($person->isRoleAtLeast(STAFF));
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
