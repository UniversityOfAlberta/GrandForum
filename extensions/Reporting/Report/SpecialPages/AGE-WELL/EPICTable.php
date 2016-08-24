<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['EPICTable'] = 'EPICTable'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['EPICTable'] = $dir . 'EPICTable.i18n.php';
$wgSpecialPageGroups['EPICTable'] = 'network-tools';

$wgHooks['SubLevelTabs'][] = 'EPICTable::createSubTabs';

function runEPICTable($par) {
    EPICTable::execute($par);
}

class EPICTable extends SpecialPage{

    function EPICTable() {
        SpecialPage::__construct("EPICTable", null, false, 'runEPICTable');
    }
    
    function userCanExecute($user){
        $person = Person::newFromUser($user);
        return ($person->isRoleAtLeast(SD) || $person->getName() == "Euson.Yeung" || $person->getName() == "Susan.Jaglal");
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
        EPICTable::generateHTML($wgOut);
    }
    
    function generateHTML($wgOut){
        global $wgUser, $wgServer, $wgScriptPath, $wgRoles, $config;
        
        $me = Person::newFromWgUser();

        $epics = array();
        $hqps = array_merge(Person::getAllPeople(HQP), 
                            Person::getAllCandidates(HQP));
        
        foreach($hqps as $hqp){
            if($hqp->isEpic()){
                $epics[] = $hqp;
            }
        }
        
        $tabbedPage = new TabbedPage("person");

        $tabbedPage->addTab(new ApplicationTab('RP_EPIC_REPORT', $epics, 0, "EPIC Survey"));

        $tabbedPage->showPage();
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        $person = Person::newFromWgUser();
        
        if(self::userCanExecute($wgUser)){
            $selected = @($wgTitle->getText() == "EPICTable") ? "selected" : false;
            $tabs["Manager"]['subtabs'][] = TabUtils::createSubTab("EPIC Surveys", "$wgServer$wgScriptPath/index.php/Special:EPICTable", $selected);
        }
        return true;
    }

}

?>
