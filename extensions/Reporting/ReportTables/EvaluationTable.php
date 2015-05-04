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
        SpecialPage::__construct("EvaluationTable", MANAGER.'+', true, 'runEvaluationTable');
    }
    
    static function show(){
        require_once('RMC2014Tab.php');
        require_once('Nominations.php');
        require_once('Productivity.php');
        require_once('ResearcherProductivity.php');
        require_once('Themes.php');
        global $wgOut, $wgUser, $wgServer, $wgScriptPath;
     
        $init_tab = 0;
        $init_tabs = array('2014' => 0);

        if(isset($_GET['year']) && isset($init_tabs[$_GET['year']])){
            $init_tab = $init_tabs[$_GET['year']];
        }
        
        $tabbedPage = new TabbedPage("tabs_rmc");
        
        $tabbedPage->addTab(new RMC2014Tab());
    
        $tabbedPage->showPage($init_tab);
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgTitle, $wgUser;
        $person = Person::newFromWgUser($wgUser);
        if($person->isRoleAtLeast(MANAGER)){
            $selected = @($wgTitle->getText() == "EvaluationTable") ? "selected" : false;
            $tabs["Manager"]['subtabs'][] = TabUtils::createSubTab("RMC Meeting", "$wgServer$wgScriptPath/index.php/Special:EvaluationTable", $selected);
        }
        return true;
    }
}

?>
