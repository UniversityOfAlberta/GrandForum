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
        return ($person->isRoleAtLeast(SD));
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
        ApplicationsTable::generateHTML($wgOut);
    }
    
    function generateHTML($wgOut){
        global $wgUser, $wgServer, $wgScriptPath, $wgRoles, $config;
        
        $nis = array_merge(Person::getAllPeople(NI), 
                           Person::getAllCandidates(NI),
                           Person::getAllPeople(EXTERNAL),
                           Person::getAllCandidates(EXTERNAL));
        
        $hqps = array_merge(Person::getAllPeople(HQP), 
                            Person::getAllCandidates(HQP));
        
        $wps = Theme::getAllThemes();
        
        $tabbedPage = new TabbedPage("person");

        $tabbedPage->addTab(new ApplicationTab('RP_SIP', $nis, 2015, "SIP 01-2016"));
        $tabbedPage->addTab(new ApplicationTab('RP_SIP_04_2016', $nis, 2015, "SIP 04-2016"));
        $tabbedPage->addTab(new ApplicationTab('RP_CAT', $nis, 2015, "Catalyst"));
        $tabbedPage->addTab(new ApplicationTab('RP_CIP', $nis, 2015, "CIP"));
        $tabbedPage->addTab(new ApplicationTab('RP_SUMMER', $hqps, 2015, "Summer Institute 2016"));
        $tabbedPage->addTab(new ApplicationTab('RP_WP_REPORT', $wps, 2015, "WP Report"));
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
