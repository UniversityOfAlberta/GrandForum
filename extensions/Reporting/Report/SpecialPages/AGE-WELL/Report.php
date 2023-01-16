<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['Report'] = 'Report'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['Report'] = $dir . 'Report.i18n.php';
$wgSpecialPageGroups['Report'] = 'reporting-tools';

require_once("CCActivitiesTable.php");
require_once("HQPRegisterTable.php");
require_once("HQPReviewTable.php");
require_once("EPICATReviewTable.php");
require_once("EEAReviewTable.php");
require_once("CRPReviewTable.php");
require_once("SIPReviewTable.php");
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
        /*if(($person->isRoleAtLeast(INACTIVE) || $person->isRoleAtLeast(INACTIVE.'-Candidate'))){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "EEA")) ? "selected" : false;
            $tabs["Applications"]['subtabs'][] = TabUtils::createSubTab("Emerging Entrepreneur", "{$url}EEA", $selected);
        }*/
        /*if($person->isRole(HQP) || $person->isRole(HQP.'-Candidate')){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "Edge")) ? "selected" : false;
            $tabs["Applications"]['subtabs'][] = TabUtils::createSubTab("Edge", "{$url}Edge", $selected);
        }*/
        if($person->isRole(HQP) | $person->isRole(HQP.'-Candidate')){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "EPICConference")) ? "selected" : false;
            $tabs["Applications"]['subtabs'][] = TabUtils::createSubTab("EPIC Conference", "{$url}EPICConference", $selected);
        }
        /*if($person->isRoleAtLeast(HQP)){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "ConferenceApplication")) ? "selected" : false;
            $tabs["Applications"]['subtabs'][] = TabUtils::createSubTab("Conference Application", "{$url}ConferenceApplication", $selected);
        }*/
        if($person->isRole(HQP) || $person->isRole(HQP.'-Candidate') ||
           $person->isRole(EXTERNAL) || $person->isRole(EXTERNAL.'-Candidate')){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "HQPApplication")) ? "selected" : false;
            $tabs["Applications"]['subtabs'][] = TabUtils::createSubTab("Award 2022", "{$url}HQPApplication", $selected);
        }
        if($person->isRole(HQP) || $person->isRole(HQP.'-Candidate')){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "AffiliateApplication")) ? "selected" : false;
            $tabs["Applications"]['subtabs'][] = TabUtils::createSubTab("Affiliate", "{$url}AffiliateApplication", $selected);
        }
        if($person->isRoleAtLeast(HQP) && $person->isEpic()){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "EPICReport")) ? "selected" : false;
            $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("Annual Report - EPIC Survey", "{$url}EPICReport", $selected);
        }
        if($person->isRole(HQP)){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "AccessApplication042023")) ? "selected" : false;
            $tabs["Applications"]['subtabs'][] = TabUtils::createSubTab("ACCESS Application", "{$url}AccessApplication042023", $selected);
        }
        
        if($person->isRole(HQP) || $person->isRole(HQP.'-Candidate')){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "SummerApplication")) ? "selected" : false;
            $tabs["Applications"]['subtabs'][] = TabUtils::createSubTab("Summer Institute", "{$url}SummerApplication", $selected);
        }
        /*if($person->isRole(HQP) || $person->isRole(HQP.'-Candidate')){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "FellowshipApplication")) ? "selected" : false;
            $tabs["Applications"]['subtabs'][] = TabUtils::createSubTab("Policy Challenge Application", "{$url}FellowshipApplication", $selected);
        }*/
        
        if($person->isRoleAtLeast(STAFF)){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "ProjectEvaluationSummary")) ? "selected" : false;
            $tabs["Manager"]['subtabs'][] = TabUtils::createSubTab("Evaluation Summary", "{$url}ProjectEvaluationSummary", $selected);
        }
        
        /*if($person->isLoggedIn()){
            $selected = @($wgTitle->getText() == "Report" && $_GET['report'] == "AIR") ? "selected" : false;
            $tabs["Applications"]['subtabs'][] = TabUtils::createSubTab("AIR", "{$url}AIR", $selected);
        }*/
        
        if($person->isSubRole('ECR')){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "ECR")) ? "selected" : false;
            $tabs["Applications"]['subtabs'][] = TabUtils::createSubTab("ECR", "{$url}ECR", $selected);
        }
        
        /*if($person->isLoggedIn()){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "EPIC-AT")) ? "selected" : false;
            $tabs["Applications"]['subtabs'][] = TabUtils::createSubTab("EPIC-AT", "{$url}EPIC-AT", $selected);
        }*/
        
        if($person->isLoggedIn()){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "EPIC-AT2023")) ? "selected" : false;
            $tabs["Applications"]['subtabs'][] = TabUtils::createSubTab("EPIC-AT 2023", "{$url}EPIC-AT2023", $selected);
        }
        
        if($person->isRoleAtLeast(STAFF)){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "ConferenceApplicationSummary")) ? "selected" : false;
            $tabs["Applications"]['subtabs'][] = TabUtils::createSubTab("Conference Application Summary", "{$url}ConferenceApplicationSummary", $selected);
        }
        
        if($person->isRole(NI) || $person->isRole(NI.'-Candidate') ||
           $person->isRole(EXTERNAL) || $person->isRole(EXTERNAL.'-Candidate') ||
           $person->isRole(STAFF)){
            /*$selected = @($wgTitle->getText() == "Report" && $_GET['report'] == "CRP") ? "selected" : false;
            $tabs["Applications"]['subtabs'][] = TabUtils::createSubTab("CRP", "{$url}CRP", $selected);*/
           
            /*$selected = @($wgTitle->getText() == "Report" && $_GET['report'] == "SIPAccelerator2019") ? "selected" : false;
            $tabs["Applications"]['subtabs'][] = TabUtils::createSubTab("SIP Accelerator", "{$url}SIPAccelerator2019", $selected);*/
            
            /*$selected = @($wgTitle->getText() == "Report" && $_GET['report'] == "CatalystApplication") ? "selected" : false;
            $tabs["Applications"]['subtabs'][] = TabUtils::createSubTab("Catalyst Application", "{$url}CatalystApplication", $selected);*/
            
            /*
            $selected = @($wgTitle->getText() == "Report" && $_GET['report'] == "CIPApplication") ? "selected" : false;
            $tabs["Applications"]['subtabs'][] = TabUtils::createSubTab("CIP Application", "{$url}CIPApplication", $selected);*/
        }
        foreach($person->getProjects() as $project){
            if ($project->getType() == 'Innovation Hub' && $person->isRoleAtLeast(NI)){
                $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "IHReport" && @$_GET['project'] == $project->getName())) ? "selected" : false;
                $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("{$project->getName()}", "{$url}IHReport&project={$project->getName()}", $selected);
            }
        }
        if($person->isRole(PL) || $person->isRole(TL) || $person->isRole(TC) || $person->isRole(PS)){
            $projects = array();
            foreach($person->leadershipDuring(NCE_START, date('Y-m-d')) as $project){
                $projects[$project->getName()] = $project;
            }
            foreach($person->getThemeProjects() as $project){
                $projects[$project->getName()] = $project;
            }
            foreach($person->getProjectsDuring(NCE_START, date('Y-m-d')) as $project){
                if($person->isRole(PS, $project)){
                    $projects[$project->getName()] = $project;
                }
            }
            knatsort($projects);
            // foreach($projects as $project){
            //     // Check for CRP project
            //     if(preg_match("/[0-9]*.[0-9]*[a-z]* .*/", $project->getName()) != 0){
            //         $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "SIPCRP")) ? "selected" : false;
            //         $tabs["Applications"]['subtabs'][] = TabUtils::createSubTab("SIP (CRP)", "{$url}SIPCRP", $selected);
            //         break;
            //     }
            // }
            foreach($projects as $project){
                if($project->getType() != 'Administrative'){
                    if(preg_match("/.*-S[0-9]+.*/", $project->getName()) != 0 ||
                       preg_match("/.*-SIP A[0-9]+.*/", $project->getName()) != 0 ||
                       preg_match("/.*-CIP[0-9]+.*/", $project->getName()) != 0 ||
                       preg_match("/.*-CAT.*/", $project->getName()) != 0 ||
                       preg_match("/AWSIP-.*/", $project->getName()) != 0 ||
                       $project->getName() == "DATcares"){
                        $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "SIPProjectEvaluation" && @$_GET['project'] == $project->getName())) ? "selected" : false;
                        $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("{$project->getName()}", "{$url}SIPProjectEvaluation&project={$project->getName()}", $selected);
                    }
                    else if(preg_match("/.*AWCRP-2020.*/", $project->getName()) != 0 ||
                            preg_match("/.*AW-PP2019.*/", $project->getName()) != 0 ||
                            preg_match("/.*AW-PP2020.*/", $project->getName()) != 0){
                        $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "CRP-PPPReport" && @$_GET['project'] == $project->getName())) ? "selected" : false;
                        $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("{$project->getName()} (Mid-Year)", "{$url}CRP-PPPReport&project={$project->getName()}", $selected);
                            
                        $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "ProjectPlanUpdate" && @$_GET['project'] == $project->getName())) ? "selected" : false;
                        $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("{$project->getName()} (Annual)", "{$url}ProjectPlanUpdate&project={$project->getName()}", $selected);
                    }
                    //else if(preg_match("/.*CAT-2019.*/", $project->getName()) != 0){
                    //    $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "CatalystReport" && @$_GET['project'] == $project->getName())) ? "selected" : false;
                    //    $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("{$project->getName()}", "{$url}CatalystReport&project={$project->getName()}", $selected);
                    //}
                    //else if(preg_match("/.*AW-PP2019.*/", $project->getName()) != 0){
                    //    $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "PlatformReport" && @$_GET['project'] == $project->getName())) ? "selected" : false;
                    //    $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("{$project->getName()}", "{$url}PlatformReport&project={$project->getName()}", $selected);
                    //}
                    else if($project->getType() != 'Administrative' && 
                            $project->getType() != 'Innovation Hub' &&
                            $project->getPhase() != 2){
                        $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "CRPReport" && @$_GET['project'] == $project->getName())) ? "selected" : false;
                        $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("{$project->getName()} (End-of-Term)", "{$url}CRPReport&project={$project->getName()}", $selected);
                    
                        //$selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "ProjectEvaluation" && @$_GET['project'] == $project->getName())) ? "selected" : false;
                        //$tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("{$project->getName()}", "{$url}ProjectEvaluation&project={$project->getName()}", $selected);
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
        /*if($person->isRole(APL) || $person->isRole(HQP)){
            $ccs = array_merge($person->leadership(), $person->getProjects());
            $alreadyDone = array();
            foreach($ccs as $cc){
                if(!isset($alreadyDone[$cc->getId()]) && $cc->getType() == 'Administrative'){
                    $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "CCReport" && @$_GET['project'] == $cc->getName())) ? "selected" : false;
                    $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("{$cc->getName()}", "{$url}CCReport&project={$cc->getName()}", $selected);
                    $alreadyDone[$cc->getId()] = true;
                }
            }
        }*/
        if(count($person->getEvaluates("EEA-2022", 2022)) > 0){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "EEAReview")) ? "selected" : false;
            $tabs["Reviews"]['subtabs'][] = TabUtils::createSubTab("EEA", "{$url}EEAReview", $selected);
        }
        if(count($person->getEvaluates("SIP-2019", 2019)) > 0){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "SIPAcceleratorReview")) ? "selected" : false;
            $tabs["Reviews"]['subtabs'][] = TabUtils::createSubTab("SIP", "{$url}SIPAcceleratorReview", $selected);
        }
        if(count($person->getEvaluates("CRP-2018", 2018)) > 0){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "CRPReview")) ? "selected" : false;
            $tabs["Reviews"]['subtabs'][] = TabUtils::createSubTab("CRP", "{$url}CRPReview", $selected);
        }
        if($person->isRole(SD) || $person->isRole(RMC) || $person->isRoleAtLeast(STAFF)){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "ProjectReviewFeedback")) ? "selected" : false;
            $tabs["Reviews"]['subtabs'][] = TabUtils::createSubTab("Project (Feedback)", "{$url}ProjectReviewFeedback", $selected);
        }
        if(count($person->getEvaluates("HQP-2022", 2022)) > 0){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "HQPReview")) ? "selected" : false;
            $tabs["Reviews"]['subtabs'][] = TabUtils::createSubTab("HQP Award", "{$url}HQPReview", $selected);
        }
        if(count($person->getEvaluates("EPIC-2022", 2022)) > 0){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "EPICATReview")) ? "selected" : false;
            $tabs["Reviews"]['subtabs'][] = TabUtils::createSubTab("EPIC-AT", "{$url}EPICATReview", $selected);
        }
        return true;
    }
    
    static function createToolboxLinks(&$toolbox){
        global $wgServer, $wgScriptPath, $config;
        return true;
    }
}

?>
