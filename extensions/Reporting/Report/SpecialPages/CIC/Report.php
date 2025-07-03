<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['Report'] = 'Report'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['Report'] = $dir . 'Report.i18n.php';
$wgSpecialPageGroups['Report'] = 'reporting-tools';

require_once("LOITable.php");

$wgHooks['TopLevelTabs'][] = 'Report::createTab';
$wgHooks['SubLevelTabs'][] = 'Report::createSubTabs';

class Report extends AbstractReport{
    
    function __construct(){
        global $config;
        $report = @$_GET['report'];
        $topProjectOnly = false;
        if(isset($_GET['project']) && ($report == "NIReport" || $report == "HQPReport" || $report == "SABReport")){
            $topProjectOnly = true;
        }
        parent::__construct(dirname(__FILE__)."/../../ReportXML/{$config->getValue('networkName')}/$report.xml", -1, false, $topProjectOnly);
    }

    static function createTab(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        $tabs["Reports"] = TabUtils::createTab("My Reports");
        $tabs["Proposals"] = TabUtils::createTab("LOI");
        return true;
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        $person = Person::newFromWgUser();
        $url = "$wgServer$wgScriptPath/index.php/Special:Report?report=";
        
        if($person->isLoggedIn()){
            $projectId = 0;
            do{
                $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "LOI") && ($_GET['project'] == $projectId || (!isset($_GET['project']) && $projectId == 0))) ? "selected" : false;
                $tabName = ($projectId > 0) ? "[".($projectId+1)."]" : "LOI&nbsp;&nbsp;&nbsp;[".($projectId+1)."]";
                $tabs["Proposals"]['subtabs'][] = TabUtils::createSubTab($tabName, "{$url}LOI&project=$projectId", $selected);
                
                $report = new DummyReport("LOI", $person, ++$projectId, 0, true);
            } while($report->hasStarted());

            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "LOI") && ($_GET['project'] == $projectId)) ? "selected" : false;
            $tabs["Proposals"]['subtabs'][] = TabUtils::createSubTab("[+]", "{$url}LOI&project=$projectId", $selected);
        }
        
        if($person->isRoleAtLeast(STAFF)){
            $selected = @($wgTitle->getText() == "Report" && $_GET['report'] == "ProjectTable") ? "selected" : false;
            $tabs["Manager"]['subtabs'][] = TabUtils::createSubTab("Project Table", "{$url}ProjectTable", $selected);
        }
        return true;
    }
    
}

?>
