<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['ApplicationsTable'] = 'ApplicationsTable'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['ApplicationsTable'] = $dir . 'ApplicationsTable.i18n.php';
$wgSpecialPageGroups['ApplicationsTable'] = 'network-tools';

$wgHooks['SubLevelTabs'][] = 'ApplicationsTable::createSubTabs';

function runApplicationsTable($par) {
    ApplicationsTable::execute($par);
}

class ApplicationsTable extends SpecialPage{

    function ApplicationsTable() {
        SpecialPage::__construct("ApplicationsTable", null, false, 'runApplicationsTable');
    }
    
    function userCanExecute($user){
        $person = Person::newFromUser($user);
        return ($person->isRoleAtLeast(SD) || count($person->getEvaluates('RP_SUMMER', 2015, "Person")));
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
        ApplicationsTable::generateHTML($wgOut);
    }
    
    function generateHTML($wgOut){
        global $wgUser, $wgServer, $wgScriptPath, $wgRoles, $config;
        
        $me = Person::newFromWgUser();
        
        $nis = array_merge(Person::getAllPeople(NI), 
                           Person::getAllCandidates(NI),
                           Person::getAllPeople(EXTERNAL),
                           Person::getAllCandidates(EXTERNAL));
        
        $hqps = array_merge(Person::getAllPeople(HQP), 
                            Person::getAllCandidates(HQP));
        
        $wps = Theme::getAllThemes();
        
        $tabbedPage = new TabbedPage("person");

        if($me->isRoleAtLeast(SD)){
            $tabbedPage->addTab(new ApplicationTab('RP_SIP', $nis, 2015, "SIP 01-2016"));
            $tabbedPage->addTab(new ApplicationTab('RP_SIP_04_2016', $nis, 2015, "SIP 04-2016"));
            $tabbedPage->addTab(new ApplicationTab('RP_CAT', $nis, 2015, "Catalyst"));
            $tabbedPage->addTab(new ApplicationTab('RP_CIP', $nis, 2015, "CIP"));
        }
        if($me->isRoleAtLeast(SD) || count($me->getEvaluates('RP_SUMMER', 2015, "Person")) > 0){
            $summerHQPs = array();
            if($me->isRoleAtLeast(SD)){
                $summerHQPs = $hqps;
            }
            else{
                foreach($hqps as $hqp){
                    if($me->isEvaluatorOf($hqp, 'RP_SUMMER', 2015, "Person")){
                        $summerHQPs[] = $hqp;
                    }
                }
            }
            $tabbedPage->addTab(new ApplicationTab('RP_SUMMER', $hqps, 2015, "Summer Institute 2016"));
        }
        if($me->isRoleAtLeast(SD)){
            $tabbedPage->addTab(new ApplicationTab('RP_WP_REPORT', $wps, 2015, "WP Report"));
        }
        $tabbedPage->showPage();
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        $person = Person::newFromWgUser();
        
        if(self::userCanExecute($wgUser)){
            $selected = @($wgTitle->getText() == "ApplicationsTable") ? "selected" : false;
            $tabs["Manager"]['subtabs'][] = TabUtils::createSubTab("Reports", "$wgServer$wgScriptPath/index.php/Special:ApplicationsTable", $selected);
        }
        return true;
    }

}

?>
