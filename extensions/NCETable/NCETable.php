<?php

autoload_register('Reporting/ReportTables');

$dir = dirname(__FILE__) . '/';

$wgSpecialPages['NCETable'] = 'NCETable';
$wgExtensionMessagesFiles['NCETable'] = $dir . 'NCETable.i18n.php';
$wgSpecialPageGroups['NCETable'] = 'report-reviewing';

$wgHooks['SubLevelTabs'][] = 'NCETable::createSubTabs';

function runNCETable($par) {
    global $wgScriptPath, $wgOut, $wgUser, $wgTitle, $_tokusers;
    NCETable::show();
}

// Ideally these would be inside the class and be used.
$_pdata;
$_pdata_loaded = false;
$_projects;

class NCETable extends SpecialPage {

    function __construct() {
        SpecialPage::__construct("NCETable", STAFF.'+', true, 'runNCETable');
    }
    
    static function show(){
        require_once('NSERCTab.php');
        require_once('NSERCRangeTab.php');
        require_once('NSERCVariableTab.php');
        require_once('NSERCRangeVariableTab.php');
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $config;
        
        $startYear = $config->getValue("projectPhaseDates");
        $startYear = substr($startYear[1], 0, 4);
        $endYear = date('Y') - 1;
        
        $tabbedPage = new TabbedPage("tabs_nserc");
        
        if($startYear != $endYear){
            $tabbedPage->addTab(new NSERCRangeTab($startYear, $endYear+1));
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
