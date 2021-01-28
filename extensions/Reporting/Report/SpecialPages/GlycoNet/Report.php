<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['Report'] = 'Report'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['Report'] = $dir . 'Report.i18n.php';
$wgSpecialPageGroups['Report'] = 'reporting-tools';

require_once("RFPApplicationTable.php");
require_once("ApplicationsTable.php");

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
        $tabs["Awards"] = TabUtils::createTab("My Awards");
        
        return true;
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        $person = Person::newFromWgUser();
        $url = "$wgServer$wgScriptPath/index.php/Special:Report?report=";
        
        // Project Leader Reports
        /*
        $leadership = $person->leadership();
        if(count($leadership) > 0){
            $projectDone = array();
            foreach($leadership as $project){
                if(!$project->isSubProject()){
                    if(isset($projectDone[$project->getName()])){
                        continue;
                    }
                    $projectDone[$project->getName()] = true;
                    if($project->getStatus() == "Proposed"){
                        $type = "ProjectProposal";
                    }
                    else{
                        continue;
                    }
                    $selected = @($wgTitle->getText() == "Report" && $_GET['report'] == "$type" && $_GET['project'] == $project->getName()) ? "selected" : false;
                    $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab($project->getName(), "{$url}$type&project={$project->getName()}", $selected);
                }
            }
        }*/
        /*if($person->isRole(NI) || $person->isRole(NI.'-Candidate') || $person->isRoleAtLeast(MANAGER)){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "CatalystReport")) ? "selected" : false;
            $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("Catalyst", "{$url}CatalystReport", $selected);
        }
        if($person->isRole(NI) || $person->isRole(NI.'-Candidate') || $person->isRoleAtLeast(MANAGER)){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "TranslationalReport")) ? "selected" : false;
            $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("Translational", "{$url}TranslationalReport", $selected);
        }*/
        /*if(count($person->getEvaluates("SAB")) > 0){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "SABReview")) ? "selected" : false;
            $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("SAB Review", "{$url}SABReview", $selected);
        }*/
        if($person->isRole(NI) || $person->isRole(HQP) || $person->isRole(FAKENI)){
            foreach($person->getProjects() as $project){
                if($person->isRole(NI, $project) || $person->isRole(HQP, $project) || $person->isRole(FAKENI, $project)){
                    $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "ProjectReport") && isset($_GET['project']) && $_GET['project'] == $project->getName()) ? "selected" : false;
                    $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("{$project->getName()}", "{$url}ProjectReport&project={$project->getName()}", $selected);
                }
            }
        }
        if(count($person->getEvaluates("SAB-Catalyst")) > 0 && !$person->isRole(RMC)){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "SABCatalystReview")) ? "selected" : false;
            $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("SAB Review", "{$url}SABCatalystReview", $selected);
        }
        else if(count($person->getEvaluates("SAB-Catalyst")) > 0 && $person->isRole(RMC)){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "SABCatalystReview")) ? "selected" : false;
            $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("RMC Review", "{$url}SABCatalystReview", $selected);
        }
        if($person->isRoleAtLeast(MANAGER) || $person->isRole(SD) || $person->isRole(RMC) || $person->getId() == 1911){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "SABCatalystReport")) ? "selected" : false;
            $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("SAB Report", "{$url}SABCatalystReport", $selected);
        }
        /*if(($person->isRoleAtLeast(MANAGER) || $person->isRole(SD))){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "SABReport")) ? "selected" : false;
            $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("SAB Report", "{$url}SABReport", $selected);
        }*/
        /*if(count($person->getEvaluates("Project")) > 0){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "RMCProjectReview")) ? "selected" : false;
            $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("RMC Review", "{$url}RMCProjectReview", $selected);
        }*/
        if($person->isRole(HQP) || $person->isRole(HQP.'-Candidate')){
            if($person->isSubRole("Research Exchange HQP")){
                $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "HQPApplications/ResearchExchangeReport")) ? "selected" : false;
                $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("Research Exchange Report", "{$url}HQPApplications/ResearchExchangeReport", $selected);
            }
            else{
                $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "HQPApplications/ResearchExchange")) ? "selected" : false;
                $tabs["Awards"]['subtabs'][] = TabUtils::createSubTab("Research Exchange", "{$url}HQPApplications/ResearchExchange", $selected);
            }
            if($person->isSubRole("Summer Award HQP")){
                $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "HQPApplications/SummerAwardReport")) ? "selected" : false;
                $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("Summer Award Report", "{$url}HQPApplications/SummerAwardReport", $selected);
            }
            else{
                $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "HQPApplications/SummerAward")) ? "selected" : false;
                $tabs["Awards"]['subtabs'][] = TabUtils::createSubTab("Summer Award", "{$url}HQPApplications/SummerAward", $selected);
            }
        }
        if($person->isRole(NI)){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "TechnologyWorkshop")) ? "selected" : false;
            $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("Tech Workshop", "{$url}TechnologyWorkshop", $selected);
            $data = DBFunctions::select(array('grand_report_blobs'),
                                        array('*'),
                                        array('rp_type'     => EQ('RP_HQP_SUMMER'),
                                              'rp_section'  => EQ('APPLICATION'),
                                              'rp_item'     => EQ('SUPNAME'),
                                              'data'        => EQ($person->getNameForForms())));
            if(count($data) > 0){
                // NI was referenced in HQP Summer Application
                $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "HQPApplications/SummerAward")) ? "selected" : false;
                $tabs["Awards"]['subtabs'][] = TabUtils::createSubTab("Summer Award", "{$url}HQPApplications/SummerAward", $selected);
            }
        }
        return true;
    }
    
    static function createToolboxLinks(&$toolbox){
        global $wgServer, $wgScriptPath, $config;

        return true;
    }
}

?>
