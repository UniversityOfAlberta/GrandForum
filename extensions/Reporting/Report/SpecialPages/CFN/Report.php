<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['Report'] = 'Report'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['Report'] = $dir . 'Report.i18n.php';
$wgSpecialPageGroups['Report'] = 'reporting-tools';

require_once("ReportStatusTable.php");
require_once("ApplicationsTable.php");

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
        $tabs["Applications"] = TabUtils::createTab("My Applications");
        $tabs["Reviews"] = TabUtils::createTab("My Reviews");
        return true;
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        $person = Person::newFromWgUser();
        $url = "$wgServer$wgScriptPath/index.php/Special:Report?report=";
        $hqps = $person->getHQP();
        $students = $person->getPeopleRelatedTo(SUPERVISES);
        $projects = $person->getProjects();
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
        if(($person->isRole(NI) || $person->isRole(NI."-Candidate")) && $person->isSubRole("KT2017Applicant")){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "KTApplication")) ? "selected" : false;
            $tabs["Applications"]['subtabs'][] = TabUtils::createSubTab("KT Application", "{$url}KTApplication", $selected);
        }
        if(($person->isRole(HQP) || $person->isRole(HQP."-Candidate")) && $person->isSubRole("IFP2017Applicant")){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "IFPApplication")) ? "selected" : false;
            $tabs["Applications"]['subtabs'][] = TabUtils::createSubTab("IFP Application", "{$url}IFPApplication", $selected);
        }
        foreach($projects as $project){
            if($person->isRole(CI, $project) && !$person->leadershipOf($project) && !$project->isDeleted()){
                if(strstr($project->getName(), "SSA2016") === false){
                    $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "FinalProjectReport" && @$_GET['project'] == $project->getName())) ? "selected" : false;
                    $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("{$project->getName()} (Final)", "{$url}FinalProjectReport&project={$project->getName()}", $selected);
                    
                    $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "ProjectProgressReport" && @$_GET['project'] == $project->getName())) ? "selected" : false;
                    $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("{$project->getName()} (Update)", "{$url}ProjectProgressReport&project={$project->getName()}", $selected);
                }
            }
        }
        foreach($person->getProjects() as $project){
            if(strstr($project->getName(), "SSA2016") !== false){
                $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "SSAReport" && @$_GET['project'] == $project->getName())) ? "selected" : false;
                $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("{$project->getName()} Final Report", "{$url}SSAReport&project={$project->getName()}", $selected);
            }
        }
        if(count($hqps) > 0){
            $processedIFP = false;
            $processedIFP2016 = false;
            foreach($hqps as $hqp){
                if($hqp->isSubRole("IFP")){
                    $ifpDeleted = false;
                    $ifp2016 = false;
                    foreach($hqp->leadership() as $project){
                        $ifpDeleted = ($ifpDeleted || ($project->isDeleted() && strstr($project->getName(), "IFP") !== false));
                        $ifp2016 = ($ifp2016 || strstr($project->getName(), "IFP2016") !== false);
                    }
                    if(!$ifpDeleted){
                        if($ifp2016 && !$processedIFP2016){
                            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "IFP2016ProgressReport")) ? "selected" : false;
                            $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("IFP2016 Progress", "{$url}IFP2016ProgressReport", $selected);
                        }
                        else if(!$ifp2016 && !$processedIFP){
                            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "IFPProgressReport")) ? "selected" : false;
                            $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("IFP Progress", "{$url}IFPProgressReport", $selected);
                        }
                    }
                    if($ifp2016 && !$processedIFP2016){
                        $processedIFP2016 = true;
                        $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "IFP2016FinalReport")) ? "selected" : false;
                        $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("IFP2016 Final", "{$url}IFP2016FinalReport", $selected);
                    }
                    else if(!$ifp2016 && !$processedIFP){
                        $processedIFP = true;
                        $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "IFPFinalReport")) ? "selected" : false;
                        $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("IFP Final", "{$url}IFPFinalReport", $selected);
                    }
                }
                
            }
        }
        if(count($students) > 0){
            $processedIFP2017 = false;
            foreach($students as $student){
                if(!$processedIFP2017 && $student->isSubRole("IFP2017Applicant")){
                    $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "IFPApplication")) ? "selected" : false;
                    $tabs["Applications"]['subtabs'][] = TabUtils::createSubTab("IFP Application", "{$url}IFPApplication", $selected);
                    $processedIFP2017 = true;
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
            if($ifp2016){
                $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "IFP2016FinalReport")) ? "selected" : false;
                $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("IFP Final", "{$url}IFP2016FinalReport", $selected);
            }
            else{
                $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "IFPFinalReport")) ? "selected" : false;
                $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("IFP Final", "{$url}IFPFinalReport", $selected);
            }
        }
        if(count($person->getEvaluates("IFP-ETC", 2017)) > 0 || $person->getName() == "Carol.Barrie" || $person->getName() == "Denise.Stockley" || $person->getName() == "Amber.Hastings-Truelove"){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "IFPReview")) ? "selected" : false;
            $tabs["Reviews"]['subtabs'][] = TabUtils::createSubTab("IFP2017 Review", "{$url}IFPReview", $selected);
        }
        /*if(count($person->getEvaluates("CAT-SRC")) > 0 || count($person->getEvaluates("CAT-EX")) > 0 || count($person->getEvaluates("CAT-RMC")) > 0){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "CatalystReview")) ? "selected" : false;
            $tabs["Reviews"]['subtabs'][] = TabUtils::createSubTab("Catalyst Review", "{$url}CatalystReview", $selected);
        }*/
        if(count($person->getEvaluates("KT-EX", 2017)) > 0 || count($person->getEvaluates("KT-KTC", 2017)) > 0 || count($person->getEvaluates("KT-RMC", 2017)) > 0){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "KTReview")) ? "selected" : false;
            $tabs["Reviews"]['subtabs'][] = TabUtils::createSubTab("KT Review", "{$url}KTReview", $selected);
        }
        if($person->isRoleAtLeast(MANAGER) || $person->getName() == "Denise.Stockley" || $person->getName() == "Amber.Hastings-Truelove"){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "ReviewReport2017")) ? "selected" : false;
            $tabs["Reviews"]['subtabs'][] = TabUtils::createSubTab("Review Report 2017", "{$url}ReviewReport2017", $selected);
        }
        if($person->isRoleAtLeast(MANAGER)){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "ReviewReport")) ? "selected" : false;
            $tabs["Reviews"]['subtabs'][] = TabUtils::createSubTab("Review Report 2015", "{$url}ReviewReport", $selected);
        }
        return true;
    }
    
    static function createToolboxLinks(&$toolbox){
        global $wgServer, $wgScriptPath, $config;

        return true;
    }
}

?>
