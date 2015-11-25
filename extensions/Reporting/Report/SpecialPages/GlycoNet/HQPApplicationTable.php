<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['HQPApplicationTable'] = 'HQPApplicationTable'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['HQPApplicationTable'] = $dir . 'HQPApplicationTable.i18n.php';
$wgSpecialPageGroups['HQPApplicationTable'] = 'network-tools';

$wgHooks['SubLevelTabs'][] = 'HQPApplicationTable::createSubTabs';

autoload_register('Reporting/Report/SpecialPages/GlycoNet/ApplicationTabs');

function runHQPApplicationTable($par) {
    HQPApplicationTable::execute($par);
}

class HQPApplicationTable extends SpecialPage{

    function HQPApplicationTable() {
        SpecialPage::__construct("HQPApplicationTable", null, false, 'runHQPApplicationTable');
    }
    
    function userCanExecute($user){
        $person = Person::newFromUser($user);
        return ($person->isRoleAtLeast(STAFF) || $person->isRole(HQPAC));
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
        HQPApplicationTable::generateHTML($wgOut);
    }
    
    function generateHTML($wgOut){
        global $wgUser, $wgServer, $wgScriptPath, $wgRoles, $config;
        
        $hqp = Person::getAllPeople(HQP);
        
        $tabbedPage = new TabbedPage("person");

        $tabbedPage->addTab(new CandidatesTab());
        $tabbedPage->addTab(new ApplicationTab('RP_HQP_EXCHANGE', $hqp));
        $tabbedPage->addTab(new ApplicationTab('RP_HQP_SUMMER', $hqp));
        $tabbedPage->showPage();
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        $person = Person::newFromWgUser();
        
        if(self::userCanExecute($wgUser)){
            $selected = @($wgTitle->getText() == "HQPApplicationTable") ? "selected" : false;
            $tabs["Manager"]['subtabs'][] = TabUtils::createSubTab("HQP Applications", "$wgServer$wgScriptPath/index.php/Special:HQPApplicationTable", $selected);
        }
        return true;
    }

}

?>
