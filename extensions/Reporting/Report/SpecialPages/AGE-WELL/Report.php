<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['Report'] = 'Report'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['Report'] = $dir . 'Report.i18n.php';
$wgSpecialPageGroups['Report'] = 'reporting-tools';

require_once("CCActivitiesTable.php");
require_once("HQPRegisterTable.php");
require_once("HQPReviewTable.php");
require_once("EPICTable.php");
require_once("ApplicationsTable.php");

$wgHooks['TopLevelTabs'][] = 'Report::createTab';
$wgHooks['SubLevelTabs'][] = 'Report::createSubTabs';
$wgHooks['ToolboxLinks'][] = 'Report::createToolboxLinks';

class Report extends AbstractReport {
    
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
        $tabs["Feedback"] = TabUtils::createTab("My Feedback");
        $tabs["Reviews"] = TabUtils::createTab("My Reviews");
        $tabs["Plans"] = TabUtils::createTab("My CC Activity Plans");
        $tabs["Applications"] = TabUtils::createTab("My Applications");
        return true;
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        $person = Person::newFromWgUser();
        $url = "$wgServer$wgScriptPath/index.php/Special:Report?report=";
        
        if($person->isRole(HQP) || $person->isRole(HQP.'-Candidate')){
            /*$selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "HQPApplication")) ? "selected" : false;
            $tabs["Applications"]['subtabs'][] = TabUtils::createSubTab("Award 2016", "{$url}HQPApplication", $selected);*/
            
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "AffiliateApplication")) ? "selected" : false;
            $tabs["Applications"]['subtabs'][] = TabUtils::createSubTab("Affiliate", "{$url}AffiliateApplication", $selected);
        }
        if($person->isRole(HQP) && $person->isEpic()){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "EPICReport")) ? "selected" : false;
            $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("Annual Report - EPIC Survey", "{$url}EPICReport", $selected);
        }
        if($person->isRole(HQP)){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "AccessApplication042017")) ? "selected" : false;
            $tabs["Applications"]['subtabs'][] = TabUtils::createSubTab("ACCESS Application", "{$url}AccessApplication042017", $selected);
        }
        /*if($person->isRole(HQP) || $person->isRole(HQP.'-Candidate')){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "SummerApplication")) ? "selected" : false;
            $tabs["Applications"]['subtabs'][] = TabUtils::createSubTab("Summer Application", "{$url}SummerApplication", $selected);
        }*/
        if($person->isRole(SD) || $person->isRole(RMC) || $person->isRoleAtLeast(STAFF)){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "ProjectReviewFeedback")) ? "selected" : false;
            $tabs["Reviews"]['subtabs'][] = TabUtils::createSubTab("Project Review (Feedback)", "{$url}ProjectReviewFeedback", $selected);
        }
        if($person->isRoleAtLeast(STAFF)){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "ProjectEvaluationSummary")) ? "selected" : false;
            $tabs["Manager"]['subtabs'][] = TabUtils::createSubTab("Evaluation Summary", "{$url}ProjectEvaluationSummary", $selected);
        }
        if($person->isRole(NI) || $person->isRole(NI.'-Candidate') ||
           $person->isRole(EXTERNAL) || $person->isRole(EXTERNAL.'-Candidate')){
            $selected = @($wgTitle->getText() == "Report" && $_GET['report'] == "SIPApplication012017") ? "selected" : false;
            $tabs["Applications"]['subtabs'][] = TabUtils::createSubTab("SIP Application", "{$url}SIPApplication012017", $selected);
            
            $selected = @($wgTitle->getText() == "Report" && $_GET['report'] == "CatalystApplication") ? "selected" : false;
            $tabs["Applications"]['subtabs'][] = TabUtils::createSubTab("Catalyst Application", "{$url}CatalystApplication", $selected);
            
            /*
            $selected = @($wgTitle->getText() == "Report" && $_GET['report'] == "CIPApplication") ? "selected" : false;
            $tabs["Applications"]['subtabs'][] = TabUtils::createSubTab("CIP Application", "{$url}CIPApplication", $selected);*/
        }
        if($person->isRole(PL) || $person->isRole(TL) || $person->isRole(TC)){
            $projects = array();
            foreach($person->leadership() as $project){
                $projects[$project->getName()] = $project;
            }
            foreach($person->getThemeProjects() as $project){
                $projects[$project->getName()] = $project;
            }
            foreach($projects as $project){
                if($project->getType() != 'Administrative'){
                    if(preg_match("/.*-S(0-9)*.*/", $project->getName()) != 0 ||
                       preg_match("/.*-CIP(0-9)*.*/", $project->getName()) != 0 ||
                       preg_match("/.*-CAT(0-9)*.*/", $project->getName()) != 0){
                        $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "SIPProjectEvaluation" && @$_GET['project'] == $project->getName())) ? "selected" : false;
                        $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("{$project->getName()}", "{$url}SIPProjectEvaluation&project={$project->getName()}", $selected);
                    }
                    else{
                        $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "ProjectEvaluation" && @$_GET['project'] == $project->getName())) ? "selected" : false;
                        $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("{$project->getName()}", "{$url}ProjectEvaluation&project={$project->getName()}", $selected);
                    }
                
                    $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "CCPlanning" && @$_GET['project'] == $project->getName())) ? "selected" : false;
                    $tabs["Plans"]['subtabs'][] = TabUtils::createSubTab("{$project->getName()}", "{$url}CCPlanning&project={$project->getName()}", $selected);
                    
                    $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "PLFeedback" && @$_GET['project'] == $project->getName())) ? "selected" : false;
                    $tabs["Feedback"]['subtabs'][] = TabUtils::createSubTab("{$project->getName()}", "{$url}PLFeedback&project={$project->getName()}", $selected);
                }
                else{
                    $report = "";
                    switch($project->getName()){
                        case "CC1 K-MOB":
                            $report = "CC1Leader";
                            break;
                        case "CC2 TECH-TRANS":
                            $report = "CC2Leader";
                            break;
                        case "CC3 T-WORK":
                            $report = "CC3Leader";
                            break;
                        case "CC4 TRAIN":
                            $report = "CC4Leader";
                            break;
                    }
                    $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == $report)) ? "selected" : false;
                    $tabs["Plans"]['subtabs'][] = TabUtils::createSubTab("{$project->getName()}", "{$url}{$report}&project={$project->getName()}", $selected);
                }
            }
        }
        if($person->isRole(TL) || $person->isRole(TC)){
            $themes = array_merge($person->getLeadThemes(), $person->getCoordThemes());
            foreach($themes as $theme){
                $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "WPReport" && @$_GET['project'] == $theme->getAcronym())) ? "selected" : false;
                $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("{$theme->getAcronym()}", "{$url}WPReport&project={$theme->getAcronym()}", $selected);
            }
        }
        if($person->isRole(APL)){
            $ccs = $person->leadership();
            foreach($ccs as $cc){
                if($cc->getType() == 'Administrative'){
                    $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "CCReport" && @$_GET['project'] == $cc->getName())) ? "selected" : false;
                    $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("{$cc->getName()}", "{$url}CCReport&project={$cc->getName()}", $selected);
                }
            }
        }
        if(count($person->getEvaluates("HQP-2016-07-15", 2016)) > 0){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "HQPReview")) ? "selected" : false;
            $tabs["Reviews"]['subtabs'][] = TabUtils::createSubTab("HQP Award 2016-07-15", "{$url}HQPReview", $selected);
        }
        return true;
    }
    
    static function createToolboxLinks(&$toolbox){
        global $wgServer, $wgScriptPath, $config;

        return true;
    }
}

?>
