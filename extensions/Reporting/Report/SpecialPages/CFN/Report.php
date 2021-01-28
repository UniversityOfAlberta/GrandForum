<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['Report'] = 'Report'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['Report'] = $dir . 'Report.i18n.php';
$wgSpecialPageGroups['Report'] = 'reporting-tools';

require_once("ReportStatusTable.php");

$wgHooks['TopLevelTabs'][] = 'Report::createTab';
$wgHooks['SubLevelTabs'][] = 'Report::createSubTabs';
$wgHooks['ToolboxLinks'][] = 'Report::createToolboxLinks';

class Report extends AbstractReport{
    
    function __construct(){
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
        $tabs["Reviews"] = TabUtils::createTab("My Reviews");
        return true;
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        $person = Person::newFromWgUser();
        $url = "$wgServer$wgScriptPath/index.php/Special:Report?report=";
        $hqps = $person->getHQPDuring(REPORTING_CYCLE_START, REPORTING_NCE_PRODUCTION);
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
                    $ssaActive = false;
                    foreach($hqp->leadership() as $project){
                        $ssaActive = ($ssaActive || (!$project->isDeleted() && strstr($project->getName(), "SSA") !== false));
                    }
                    if($ssaActive){
                        $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "HQPReport")) ? "selected" : false;
                        $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("SSA Report", "{$url}HQPReport", $selected);
                    }
                    break;
                }
            }
            foreach($hqps as $hqp){
                if($hqp->isSubRole("IFP")){
                    $ifpDeleted = false;
                    $ifp2016 = false;
                    foreach($hqp->leadership() as $project){
                        $ifpDeleted = ($ifpDeleted || ($project->isDeleted() && strstr($project->getName(), "IFP") !== false));
                        $ifp2016 = ($ifp2016 || strstr($project->getName(), "IFP2016") !== false);
                    }
                    if(!$ifpDeleted){
                        if($ifp2016){
                            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "IFP2016ProgressReport")) ? "selected" : false;
                            $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("IFP Progress", "{$url}IFP2016ProgressReport", $selected);
                        }
                        else{
                            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "IFPProgressReport")) ? "selected" : false;
                            $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("IFP Progress", "{$url}IFPProgressReport", $selected);
                        }
                    }
                    if(!$ifp2016){
                        $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "IFPFinalReport")) ? "selected" : false;
                        $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("IFP Final", "{$url}IFPFinalReport", $selected);
                    }
                    break;
                }
            }
        }
        if($person->isSubRole('IFP')){
            $ifpDeleted = false;
            $ifp2016 = false;
            foreach($person->leadership() as $project){
                $ifpDeleted = ($ifpDeleted || ($project->isDeleted() && strstr($project->getName(), "IFP") !== false));
                $ifp2016 = ($ifp2016 || strstr($project->getName(), "IFP2016") !== false);
            }
            if(!$ifpDeleted){
                if($ifp2016){
                    $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "IFP2016ProgressReport")) ? "selected" : false;
                    $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("IFP Progress", "{$url}IFP2016ProgressReport", $selected);
                }
                else{
                    $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "IFPProgressReport")) ? "selected" : false;
                    $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("IFP Progress", "{$url}IFPProgressReport", $selected);
                }
            }
            if(!$ifp2016){
                $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "IFPFinalReport")) ? "selected" : false;
                $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("IFP Final", "{$url}IFPFinalReport", $selected);
            }
        }
        if(count($person->getEvaluates("IFP-ETC")) > 0){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "IFPReview")) ? "selected" : false;
            $tabs["Reviews"]['subtabs'][] = TabUtils::createSubTab("IFP2016 Review", "{$url}IFPReview", $selected);
        }
        if(count($person->getEvaluates("CAT-SRC")) > 0 || count($person->getEvaluates("CAT-EX")) > 0 || count($person->getEvaluates("CAT-RMC")) > 0){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "CatalystReview")) ? "selected" : false;
            $tabs["Reviews"]['subtabs'][] = TabUtils::createSubTab("Catalyst Review", "{$url}CatalystReview", $selected);
        }
        /*if(count($person->getEvaluates("TRANS-SRC")) > 0 || count($person->getEvaluates("TRANS-EX")) > 0 || count($person->getEvaluates("TRANS-RMC")) > 0){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "TransformativeReview")) ? "selected" : false;
            $tabs["Reviews"]['subtabs'][] = TabUtils::createSubTab("Transformative Review", "{$url}TransformativeReview", $selected);
        }*/
        if($person->isRoleAtLeast(MANAGER)){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "ReviewReport")) ? "selected" : false;
            $tabs["Reviews"]['subtabs'][] = TabUtils::createSubTab("Review Report", "{$url}ReviewReport", $selected);
        }
        return true;
    }
    
    static function createToolboxLinks(&$toolbox){
        global $wgServer, $wgScriptPath, $config;

        return true;
    }
}

?>
