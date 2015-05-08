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
        wfLoadExtensionMessages('NCETable');
        SpecialPage::SpecialPage("NCETable", MANAGER.'+', true, 'runNCETable');
    }
    
    static function show(){
        require_once('NSERCTab.php');
        require_once('NSERCRangeTab.php');
        require_once('NSERCVariableTab.php');
        require_once('NSERCRangeVariableTab.php');
        //require_once('NSERC2012Tab.php');
        //require_once('NSERC2011Tab.php');
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $config;
     
        $init_tab = 0;
        $init_tabs = array('Jan-Dec2012' => 0, 
                           'Apr2012-Mar2013' => 1, 
                           'Jan-Mar2012' => 2, 
                           'Apr-Dec2012' => 3, 
                           'Jan-Mar2013' => 4, 
                           '2012' => 5, 
                           '2011' => 6);

        if(isset($_GET['year']) && isset($init_tabs[$_GET['year']])){
            $init_tab = $init_tabs[$_GET['year']];
        }
        
        $startYear = $config->getValue("projectPhaseDates");
        $startYear = substr($startYear[1], 0, 4);
        
        $tabbedPage = new TabbedPage("tabs_nserc");
        
        if($startYear != YEAR+1){
            $tabbedPage->addTab(new NSERCRangeTab($startYear, YEAR+1));
        }
        for($year = YEAR+1; $year >= $startYear; $year--){
            $tabbedPage->addTab(new NSERCTab($year));
        }
        
        /* // This is the old version of the NCETables
        $tabbedPage->addTab(new NSERCTab(2014));
        $tabbedPage->addTab(new NSERCTab(2013));

        $tabbedPage->addTab(new NSERC2012Tab());
        $tabbedPage->addTab(new NSERC2011Tab());
        */
        $tabbedPage->showPage($init_tab);
    }
    
    static function createSubTabs($tabs){
        global $wgServer, $wgScriptPath, $wgTitle, $wgUser;
        $person = Person::newFromWgUser($wgUser);
        if($person->isRoleAtLeast(MANAGER)){
            $selected = @($wgTitle->getText() == "NCETable") ? "selected" : false;
            $tabs["Manager"]['subtabs'][] = TabUtils::createSubTab("NCE", "$wgServer$wgScriptPath/index.php/Special:NCETable", $selected);
        }
        return true;
    }
}

?>
