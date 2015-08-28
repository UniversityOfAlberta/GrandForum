<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['Report'] = 'Report'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['Report'] = $dir . 'Report.i18n.php';
$wgSpecialPageGroups['Report'] = 'reporting-tools';

$wgHooks['TopLevelTabs'][] = 'Report::createTab';
$wgHooks['SubLevelTabs'][] = 'Report::createSubTabs';
$wgHooks['ToolboxLinks'][] = 'Report::createToolboxLinks';

class Report extends AbstractReport{
    
    function Report(){
        global $config;
        $report = @$_GET['report'];
        $topProjectOnly = false;
        if(isset($_GET['project']) && ($report == "NIReport" || $report == "HQPReport" || $report == "SABReport")){
            $topProjectOnly = true;
        }
        $this->AbstractReport(dirname(__FILE__)."/../../ReportXML/{$config->getValue('networkName')}/$report.xml", -1, false, $topProjectOnly);
    }

    static function createTab(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        $tabs["Reports"] = TabUtils::createTab("My Reports");
        return true;
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        $person = Person::newFromWgUser();
        $url = "$wgServer$wgScriptPath/index.php/Special:Report?report=";
        if($person->isRole(PL)){
            foreach($person->leadership() as $project){
                $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "FinalProjectReport" && @$_GET['project'] == $project->getName())) ? "selected" : false;
                $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("{$project->getName()} (Final)", "{$url}FinalProjectReport&project={$project->getName()}", $selected);
                
                $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "ProjectProgressReport" && @$_GET['project'] == $project->getName())) ? "selected" : false;
                $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("{$project->getName()} (Update)", "{$url}ProjectProgressReport&project={$project->getName()}", $selected);
                
                $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "IFPProgressReport" && @$_GET['project'] == $project->getName())) ? "selected" : false;
                $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("{$project->getName()} (IFP Update)", "{$url}IFPProgressReport&project={$project->getName()}", $selected);
                
                $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "IFPFinalReport" && @$_GET['project'] == $project->getName())) ? "selected" : false;
                $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("{$project->getName()} (IFP Final)", "{$url}IFPFinalReport&project={$project->getName()}", $selected);
            }
        }
        if($person->isRole(NI)){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "HQPReport")) ? "selected" : false;
            $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("HQP Report", "{$url}HQPReport", $selected);
        }
        return true;
    }
    
    static function createToolboxLinks(&$toolbox){
        global $wgServer, $wgScriptPath, $config;

        return true;
    }
}

?>
