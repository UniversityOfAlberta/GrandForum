<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['Report'] = 'Report'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['Report'] = $dir . 'Report.i18n.php';
$wgSpecialPageGroups['Report'] = 'reporting-tools';

//require_once("RFPApplicationTable.php");
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
        $tabs["Proposals"] = TabUtils::createTab("My Proposals");
        $tabs["Awards"] = TabUtils::createTab("My Awards");
        $tabs["Reviews"] = TabUtils::createTab("My Reviews");
        
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
        if($person->isRole(NI) || $person->isRole(NI.'-Candidate') || 
           $person->isRole(EXTERNAL) || $person->isRole(EXTERNAL.'-Candidate')){
            //$selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "CatalystReport")) ? "selected" : false;
            //$tabs["Proposals"]['subtabs'][] = TabUtils::createSubTab("Catalyst", "{$url}CatalystReport", $selected);
            
            //$selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "CollaborativeReport082017")) ? "selected" : false;
            //$tabs["Proposals"]['subtabs'][] = TabUtils::createSubTab("Collaborative", "{$url}CollaborativeReport082017", $selected);
            
            //$selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "TranslationalReport")) ? "selected" : false;
            //$tabs["Proposals"]['subtabs'][] = TabUtils::createSubTab("Translational", "{$url}TranslationalReport", $selected);
            
            //$selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "CollaborativeLOI")) ? "selected" : false;
            //$tabs["Proposals"]['subtabs'][] = TabUtils::createSubTab("Collaborative LOI", "{$url}CollaborativeLOI", $selected);
            /*if($person->isSubRole('Alberta2019')){
                $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "AlbertaReport")) ? "selected" : false;
                $tabs["Proposals"]['subtabs'][] = TabUtils::createSubTab("Alberta", "{$url}AlbertaReport", $selected);
            }*/
        }
        /*if($person->isSubRole('Collab2018')){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "CollaborativeReport2018")) ? "selected" : false;
            $tabs["Proposals"]['subtabs'][] = TabUtils::createSubTab("Collaborative", "{$url}CollaborativeReport2018", $selected);
        }*/
        if($person->isSubRole('Strat2019')){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "StrategicReport")) ? "selected" : false;
            $tabs["Proposals"]['subtabs'][] = TabUtils::createSubTab("Strategic", "{$url}StrategicReport", $selected);
        }
        if($person->isSubRole('StartUpLegal2018')){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "StartUpLegal")) ? "selected" : false;
            $tabs["Proposals"]['subtabs'][] = TabUtils::createSubTab("Start Up (Legal)", "{$url}StartUpLegal", $selected);
        }
        if($person->isSubRole('StartUpDev2018')){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "StartUpDevelopment")) ? "selected" : false;
            $tabs["Proposals"]['subtabs'][] = TabUtils::createSubTab("Start Up (Dev)", "{$url}StartUpDevelopment", $selected);
        }
        /*if(count($person->getEvaluates("SAB")) > 0){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "SABReview")) ? "selected" : false;
            $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("SAB Review", "{$url}SABReview", $selected);
        }*/
        if($person->isRole(NI) || $person->isRole(HQP) || $person->isRole(FAKENI)){
            foreach($person->getProjects() as $project){
                if($person->leadershipOf($project) || $person->isRole(NI, $project) || $person->isRole(HQP, $project) || $person->isRole(FAKENI, $project)){
                    $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "ProjectReport") && isset($_GET['project']) && $_GET['project'] == $project->getName()) ? "selected" : false;
                    $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("{$project->getName()}", "{$url}ProjectReport&project={$project->getName()}", $selected);
                }
            }
        }
        if(count($person->getEvaluates("SAB-Alberta", 2019)) > 0){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "SABAlbertaReview")) ? "selected" : false;
            $tabs["Reviews"]['subtabs'][] = TabUtils::createSubTab("Alberta Review", "{$url}SABAlbertaReview", $selected);
        }
        if(count($person->getEvaluates("SAB-Collaborative2018", 2018)) > 0){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "SABCollaborative2018Review")) ? "selected" : false;
            $tabs["Reviews"]['subtabs'][] = TabUtils::createSubTab("Collab Review", "{$url}SABCollaborative2018Review", $selected);
        }
        if(count($person->getEvaluates("SAB-Collaborative", 2018)) > 0){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "SABCollaborativeReview")) ? "selected" : false;
            $tabs["Reviews"]['subtabs'][] = TabUtils::createSubTab("Collab LOI Review", "{$url}SABCollaborativeReview", $selected);
        }
        if(count($person->getEvaluates("SAB-Strat", 2019)) > 0){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "SABStrategicReview")) ? "selected" : false;
            $tabs["Reviews"]['subtabs'][] = TabUtils::createSubTab("Strategic Review", "{$url}SABStrategicReview", $selected);
        }
        if(count($person->getEvaluates("SAB-Translational")) > 0){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "SABTranslationalReview")) ? "selected" : false;
            $tabs["Reviews"]['subtabs'][] = TabUtils::createSubTab("Translational Review", "{$url}SABTranslationalReview", $selected);
        }
        if(count($person->getEvaluates("SAB-Catalyst", 2017)) > 0){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "SABCatalystReview")) ? "selected" : false;
            $tabs["Reviews"]['subtabs'][] = TabUtils::createSubTab("Catalyst Review", "{$url}SABCatalystReview", $selected);
        }
        if($person->isRoleAtLeast(MANAGER) || 
           $person->isRole(SD) || 
           $person->isRole(RMC) ||
           $person->isRoleAtLeast(STAFF) ||
           $person->getId() == 2513){
            if($person->isRoleAtLeast(MANAGER) || 
               $person->isRole(SD) || 
               $person->isRole(RMC) ||
               $person->isRoleAtLeast(STAFF)){
                $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "SABAlbertaReport")) ? "selected" : false;
                $tabs["Reviews"]['subtabs'][] = TabUtils::createSubTab("Alberta Report", "{$url}SABAlbertaReport", $selected);
            }
            
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "SABCollaborative2018Report")) ? "selected" : false;
            $tabs["Reviews"]['subtabs'][] = TabUtils::createSubTab("Collab Report", "{$url}SABCollaborative2018Report", $selected);
            
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "SABStrategicReport")) ? "selected" : false;
            $tabs["Reviews"]['subtabs'][] = TabUtils::createSubTab("Strategic Report", "{$url}SABStrategicReport", $selected);
            
            if($person->isRoleAtLeast(MANAGER) || 
               $person->isRole(SD) || 
               $person->isRoleAtLeast(STAFF) ||
               $person->getId() == 2513){
                $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "SABTranslationalReport")) ? "selected" : false;
                $tabs["Reviews"]['subtabs'][] = TabUtils::createSubTab("Translational Report", "{$url}SABTranslationalReport", $selected);
            }
           
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "SABCatalystReport")) ? "selected" : false;
            $tabs["Reviews"]['subtabs'][] = TabUtils::createSubTab("Catalyst Report", "{$url}SABCatalystReport", $selected);
            
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "SABCollaborativeReport")) ? "selected" : false;
            $tabs["Reviews"]['subtabs'][] = TabUtils::createSubTab("Collab LOI Report", "{$url}SABCollaborativeReport", $selected);
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
            if($person->isSubRole("Summer Award HQP")){
                $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "HQPApplications/SummerAwardReport")) ? "selected" : false;
                $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("Summer Award Report", "{$url}HQPApplications/SummerAwardReport", $selected);
            }
            if($person->isSubRole("ATOP HQP")){
                $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "HQPApplications/ATOPReport")) ? "selected" : false;
                $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("ATOP Report", "{$url}HQPApplications/ATOPReport", $selected);
            }

            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "HQPApplications/ResearchExchange")) ? "selected" : false;
            $tabs["Awards"]['subtabs'][] = TabUtils::createSubTab("Research Exchange", "{$url}HQPApplications/ResearchExchange", $selected);
            
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "HQPApplications/SummerAward")) ? "selected" : false;
            $tabs["Awards"]['subtabs'][] = TabUtils::createSubTab("Summer Award", "{$url}HQPApplications/SummerAward", $selected);
            
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "HQPApplications/ATOP")) ? "selected" : false;
            $tabs["Awards"]['subtabs'][] = TabUtils::createSubTab("ATOP", "{$url}HQPApplications/ATOP", $selected);
        }
        /*if($person->isRole(NI) || $person->isRole(HQP)){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "RegionalMeeting")) ? "selected" : false;
            $tabs["Proposals"]['subtabs'][] = TabUtils::createSubTab("Regional Meeting", "{$url}RegionalMeeting", $selected);
        }*/
        if($person->isRole(NI)){
            /*$selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "TechnologyWorkshop")) ? "selected" : false;
            $tabs["Proposals"]['subtabs'][] = TabUtils::createSubTab("Tech Workshop", "{$url}TechnologyWorkshop", $selected);*/
            
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "SeminarSeries")) ? "selected" : false;
            $tabs["Proposals"]['subtabs'][] = TabUtils::createSubTab("Seminar Series", "{$url}SeminarSeries", $selected);
            
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
