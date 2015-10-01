<?php

autoload_register('Reporting/ReportTables');

$dir = dirname(__FILE__) . '/';

$wgSpecialPages['EvaluationTable'] = 'EvaluationTable';
$wgExtensionMessagesFiles['EvaluationTable'] = $dir . 'EvaluationTable.i18n.php';
$wgSpecialPageGroups['EvaluationTable'] = 'report-reviewing';

$wgHooks['SubLevelTabs'][] = 'EvaluationTable::createSubTabs';

function runEvaluationTable($par) {
    global $wgScriptPath, $wgOut, $wgUser, $wgTitle, $_tokusers;
    EvaluationTable::show();
}

// Ideally these would be inside the class and be used.
$_pdata;
$_pdata_loaded = false;
$_projects;

class EvaluationTable extends SpecialPage {

    function __construct() {
        parent::__construct("EvaluationTable", null, false, 'runEvaluationTable');
    }
    
    function userCanExecute($user){
        $person = Person::newFromWgUser();
        return ($person->isRoleAtLeast(STAFF) || $person->isRole(SD));
    }
    
    static function show(){
        require_once('RMC2015Tab.php');
        require_once('Nominations.php');

        $init_tabs = array('2015' => 0);
        $init_tab = $init_tabs[REPORTING_YEAR];
        if(isset($_GET['year']) && isset($init_tabs[$_GET['year']])){
            $init_tab = $init_tabs[$_GET['year']];
        }
        
        $tabbedPage = new TabbedPage("tabs_rmc");
        
        $tabbedPage->addTab(new RMC2015Tab());
    
        $tabbedPage->showPage($init_tab);
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgTitle, $wgUser;
        if(EvaluationTable::userCanExecute($wgUser)){
            $selected = @($wgTitle->getText() == "EvaluationTable") ? "selected" : false;
            $tabs["Manager"]['subtabs'][] = TabUtils::createSubTab("RMC Meeting", "$wgServer$wgScriptPath/index.php/Special:EvaluationTable", $selected);
        }
        return true;
    }
}

?>
