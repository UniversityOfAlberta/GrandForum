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
        return ($person->isRoleAtLeast(STAFF));
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
        ApplicationsTable::generateHTML($wgOut);
    }
    
    function generateHTML($wgOut){
        global $wgUser, $wgServer, $wgScriptPath, $wgRoles, $config;
        
        $hqp = array_merge(Person::getAllPeople(HQP), Person::getAllCandidates(HQP));
        $ni = Person::getAllPeople(NI);
        $allNis = array_merge($ni, 
                              Person::getAllCandidates(NI), 
                              Person::getAllPeople(EXTERNAL), 
                              Person::getAllCandidates(EXTERNAL));
        $projects = Project::getAllProjects();
        
        $tabbedPage = new TabbedPage("person");

        $tabbedPage->addTab(new CandidatesTab());
        $tabbedPage->addTab(new ApplicationTab(array(RP_CATALYST), $allNis, 2015, "Cat 2015"));
        $tabbedPage->addTab(new ApplicationTab(array(RP_TRANS), $allNis, 2015, "Trans 2015"));
        $tabbedPage->addTab(new ApplicationTab(array(RP_CATALYST), $allNis, 2016, "Cat 2016"));
        $tabbedPage->addTab(new ApplicationTab(array('RP_COLLAB'), $allNis, 2016, "Collab 2016"));
        $tabbedPage->addTab(new ApplicationTab(array('RP_HQP_EXCHANGE', 'RP_HQP_EXCHANGE_REPORT'), $hqp, 2015, "Research Exchange"));
        $tabbedPage->addTab(new ApplicationTab(array('RP_HQP_SUMMER', 'RP_HQP_SUMMER_REPORT'), $hqp, 2015, "Summer"));
        $tabbedPage->addTab(new ApplicationTab(array('RP_TECH_WORKSHOP'), $ni, 2015, "Tech Workshop"));
        $tabbedPage->addTab(new ApplicationTab(array('RP_REGIONAL_MEETING'), array_merge(Person::getAllPeople(HQP), $ni), 2015, "Regional Meeting"));
        $tabbedPage->addTab(new ApplicationTab(array(RP_PROGRESS), $projects, 2015, "Project Report"));
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
