<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['Report'] = 'Report'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['Report'] = $dir . 'Report.i18n.php';
$wgSpecialPageGroups['Report'] = 'reporting-tools';

require_once("ApplicationsTable.php");

$wgHooks['TopLevelTabs'][] = 'Report::createTab';
$wgHooks['SubLevelTabs'][] = 'Report::createSubTabs';
$wgHooks['ToolboxLinks'][] = 'Report::createToolboxLinks';

class Report extends AbstractReport {
    
    function Report(){
        global $config;
        $report = @$_GET['report'];
        $topProjectOnly = false;
        if(isset($_GET['project'])){
            $topProjectOnly = true;
        }
        $this->AbstractReport(dirname(__FILE__)."/../../ReportXML/{$config->getValue('networkName')}/$report.xml", -1, false, $topProjectOnly);
    }

    static function createTab(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        $tabs["Reports"] = TabUtils::createTab("My Reports");
        $tabs["Applications"] = TabUtils::createTab("My Applications");
        return true;
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        $person = Person::newFromWgUser();
        $url = "$wgServer$wgScriptPath/index.php/Special:Report?report=";
        
        if($person->isLoggedIn()){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "EOI" && $_GET['project'] == 1)) ? "selected" : false;
            $tabs["Applications"]['subtabs'][] = TabUtils::createSubTab("EOI 1", "{$url}EOI&project=1", $selected);
            
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "EOI" && $_GET['project'] == 2)) ? "selected" : false;
            $tabs["Applications"]['subtabs'][] = TabUtils::createSubTab("EOI 2", "{$url}EOI&project=2", $selected);
            
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "EOI" && $_GET['project'] == 3)) ? "selected" : false;
            $tabs["Applications"]['subtabs'][] = TabUtils::createSubTab("EOI 3", "{$url}EOI&project=3", $selected);
        }
        
        return true;
    }
    
    static function createToolboxLinks(&$toolbox){
        global $wgServer, $wgScriptPath, $config;

        return true;
    }
}

?>
