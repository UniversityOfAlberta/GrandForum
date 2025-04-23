<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['QACVGenerator'] = 'QACVGenerator'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['QACVGenerator'] = $dir . 'QACVGenerator.i18n.php';
$wgSpecialPageGroups['QACVGenerator'] = 'network-tools';

$wgHooks['SubLevelTabs'][] = 'QACVGenerator::createSubTabs';

class QACVGenerator extends SpecialPage{

    function __construct() {
        parent::__construct("QACVGenerator", STAFF.'+', true);
    }

    function execute($par){
        global $wgOut, $wgServer, $wgScriptPath, $facultyMapSimple;
        $tabbedPage = new TabbedAjaxPage("qacv");
        $person = Person::newFromWgUser();
        
        $departments = $facultyMapSimple[getFaculty()];
        foreach($departments as $key => $department){
            $tabbedPage->addTab(new QACVDepartmentTab($department, array($key)));
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
        /*if((new self)->userCanExecute($wgUser)){
            $selected = ($wgTitle->getNSText() == "Special" && ($wgTitle->getText() == "QACVGenerator")) ? "selected" : "";
            $tabs['Manager']['subtabs'][] = TabUtils::createSubTab("QACV Generator", 
                                                                   "$wgServer$wgScriptPath/index.php/Special:QACVGenerator", 
                                                                   "$selected");
        }*/
    }
    
}

?>
