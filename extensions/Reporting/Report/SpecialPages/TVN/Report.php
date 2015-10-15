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
        $hqps = $person->getHQPDuring(REPORTING_CYCLE_START, REPORTING_CYCLE_END);
        $projects = $person->getProjectsDuring(REPORTING_CYCLE_START, REPORTING_CYCLE_END);
        if($person->isRole(PL) && !$person->isRole(HQP)){
            foreach($person->leadership() as $project){
                if(!$project->isDeleted()){
                    $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "FinalProjectReport" && @$_GET['project'] == $project->getName())) ? "selected" : false;
                    $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("{$project->getName()} (Final)", "{$url}FinalProjectReport&project={$project->getName()}", $selected);
                    
                    $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "ProjectProgressReport" && @$_GET['project'] == $project->getName())) ? "selected" : false;
                    $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("{$project->getName()} (Update)", "{$url}ProjectProgressReport&project={$project->getName()}", $selected);
                }
            }
        }
        foreach($projects as $project){
            if($person->isRoleDuring(CI, REPORTING_CYCLE_START, REPORTING_CYCLE_END, $project) && !$person->leadershipOf($project) && !$project->isDeleted()){
                $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "FinalProjectReport" && @$_GET['project'] == $project->getName())) ? "selected" : false;
                $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("{$project->getName()} (Final)", "{$url}FinalProjectReport&project={$project->getName()}", $selected);
                
                $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "ProjectProgressReport" && @$_GET['project'] == $project->getName())) ? "selected" : false;
                $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("{$project->getName()} (Update)", "{$url}ProjectProgressReport&project={$project->getName()}", $selected);
            }
        }
        if(count($hqps) > 0){
            foreach($hqps as $hqp){
                if($hqp->isSubRole("SSA")){
                    $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "HQPReport")) ? "selected" : false;
                    $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("SSA Report", "{$url}HQPReport", $selected);
                    break;
                }
            }
            foreach($hqps as $hqp){
                if($hqp->isSubRole("IFP")){
                    $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "IFPProgressReport")) ? "selected" : false;
                    $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("IFP Progress", "{$url}IFPProgressReport", $selected);
                    
                    $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "IFPFinalReport")) ? "selected" : false;
                    $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("IFP Final", "{$url}IFPFinalReport", $selected);
                    break;
                }
            }
        }
        if($person->isSubRole('IFP')){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "IFPProgressReport")) ? "selected" : false;
            $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("IFP Progress", "{$url}IFPProgressReport", $selected);
            
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "IFPFinalReport")) ? "selected" : false;
            $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("IFP Final", "{$url}IFPFinalReport", $selected);
        }
        return true;
    }
    
    static function createToolboxLinks(&$toolbox){
        global $wgServer, $wgScriptPath, $config;

        return true;
    }
}

?>
