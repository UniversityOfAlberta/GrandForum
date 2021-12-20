<?php

autoload_register('Reporting/ReportTables');

$dir = dirname(__FILE__) . '/';

$wgSpecialPages['NCETable'] = 'NCETable';
$wgExtensionMessagesFiles['NCETable'] = $dir . 'NCETable.i18n.php';
$wgSpecialPageGroups['NCETable'] = 'report-reviewing';

$wgHooks['SubLevelTabs'][] = 'NCETable::createSubTabs';

// Ideally these would be inside the class and be used.
$_pdata;
$_pdata_loaded = false;
$_projects;

class NCETable extends SpecialPage {

    function __construct() {
        SpecialPage::__construct("NCETable", STAFF.'+', true);
    }
    
    function execute($par){
        require_once('NSERCTab.php');
        require_once('NSERCVariableTab.php');
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $config;
        $this->getOutput()->setPageTitle("NCE Table");
        $startYear = $config->getValue("projectPhaseDates");
        $startYear = substr($startYear[1], 0, 4);
        $endYear = date('Y') - 1;
        
        $tabbedPage = new TabbedPage("tabs_nserc");
        
        if($startYear != $endYear){
            $int_start = "{$startYear}-01-01 00:00:00";
            $int_end = ($endYear+1)."-03-31 00:00:00";
            $tabbedPage->addTab(new NSERCVariableTab("{$startYear}-".($endYear+1), $int_start, $int_end, 1));
        }
        for($year = $endYear+1; $year >= $startYear; $year--){
            $tabbedPage->addTab(new NSERCTab($year));
        }
        
        $tabbedPage->showPage();
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgTitle, $wgUser;
        $person = Person::newFromWgUser($wgUser);
        if($person->isRoleAtLeast(STAFF)){
            $selected = @($wgTitle->getText() == "NCETable") ? "selected" : false;
            array_splice($tabs["Manager"]['subtabs'], 0, 0, array(TabUtils::createSubTab("NCE", "$wgServer$wgScriptPath/index.php/Special:NCETable", $selected)));
        }
        return true;
    }
}

?>
