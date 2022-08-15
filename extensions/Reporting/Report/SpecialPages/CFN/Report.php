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
        $tabs["Applications"] = TabUtils::createTab("My Applications");
        $tabs["Reviews"] = TabUtils::createTab("My Reviews");
        $tabs["Surveys"] = TabUtils::createTab("My Surveys");
        return true;
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        $person = Person::newFromWgUser();
        $url = "$wgServer$wgScriptPath/index.php/Special:Report?report=";
        $hqps = $person->getHQP();
        $students = $person->getPeopleRelatedTo(SUPERVISES);
        $projects = $person->getProjects();
        if(($person->isRole(PL) || $person->isRole(PS)) && !$person->isRole(HQP)){
            $projectsDone = array();
            foreach($person->leadership() as $project){
                if(!$project->isDeleted()){
                    $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "FinalProjectReport" && @$_GET['project'] == $project->getName())) ? "selected" : false;
                    $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("{$project->getName()} (Final)", "{$url}FinalProjectReport&project={$project->getName()}", $selected);
                    
                    $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "ProjectProgressReport" && @$_GET['project'] == $project->getName())) ? "selected" : false;
                    $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("{$project->getName()} (Update)", "{$url}ProjectProgressReport&project={$project->getName()}", $selected);
                    $projectsDone[$project->getId()] = true;
                }
            }
            foreach($projects as $project){
                if(!isset($projectsDone[$project->getId()]) && $person->isRole(PS, $project) && !$project->isDeleted()){
                    $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "FinalProjectReport" && @$_GET['project'] == $project->getName())) ? "selected" : false;
                    $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("{$project->getName()} (Final)", "{$url}FinalProjectReport&project={$project->getName()}", $selected);
                    
                    $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "ProjectProgressReport" && @$_GET['project'] == $project->getName())) ? "selected" : false;
                    $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("{$project->getName()} (Update)", "{$url}ProjectProgressReport&project={$project->getName()}", $selected);
                    $projectsDone[$project->getId()] = true;
                }
            }
        }
        /*if($person->isSubRole("KT2019Applicant")){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "KT2019Application")) ? "selected" : false;
            $tabs["Applications"]['subtabs'][] = TabUtils::createSubTab("KT Application", "{$url}KT2019Application", $selected);
        }*/
        /*if(($person->isRole(NI) || $person->isRole(NI."-Candidate")) && $person->isSubRole("CAT2018Applicant")){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "Catalyst2018Application")) ? "selected" : false;
            $tabs["Applications"]['subtabs'][] = TabUtils::createSubTab("CAT Application", "{$url}Catalyst2018Application", $selected);
        }*/
        /*if($person->isSubRole("ECR2022Applicant")){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "EarlyCareerApplication")) ? "selected" : false;
            $tabs["Applications"]['subtabs'][] = TabUtils::createSubTab("ECR Application", "{$url}EarlyCareerApplication", $selected);
        }*/
        if($person->isSubRole("RCHA2021Applicant")){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "RCHA2021Application")) ? "selected" : false;
            $tabs["Applications"]['subtabs'][] = TabUtils::createSubTab("RCHA Application", "{$url}RCHA2021Application", $selected);
        }
        /*if($person->isRole(NI) || $person->isRole(NI."-Candidate") || $person->isRole(INACTIVE) || $person->isRole(INACTIVE."-Candidate")){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "EarlyCareerIntent")) ? "selected" : false;
            $tabs["Applications"]['subtabs'][] = TabUtils::createSubTab("Early Career LOI", "{$url}EarlyCareerIntent", $selected);
        }*/
        /*if($person->isRole(NI) || $person->isRole(NI."-Candidate") || $person->isRole(INACTIVE) || $person->isRole(INACTIVE."-Candidate") || $person->getId() == "3124"){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "RCHA2021Intent")) ? "selected" : false;
            $tabs["Applications"]['subtabs'][] = TabUtils::createSubTab("RCHA Intent", "{$url}RCHA2021Intent", $selected);
        }*/
        if(($person->isRole(HQP) || $person->isRole(HQP."-Candidate")) && $person->isSubRole("IFP2020Applicant")){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "IFPApplication")) ? "selected" : false;
            $tabs["Applications"]['subtabs'][] = TabUtils::createSubTab("IFP Application", "{$url}IFPApplication", $selected);
        }
        foreach($projects as $project){
            if($person->isRole(CI, $project) && !$person->isRole(PL, $project) && !$project->isDeleted()){
                if(strstr($project->getName(), "SSA20") === false){
                    $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "FinalProjectReport" && @$_GET['project'] == $project->getName())) ? "selected" : false;
                    $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("{$project->getName()} (Final)", "{$url}FinalProjectReport&project={$project->getName()}", $selected);
                    
                    $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "ProjectProgressReport" && @$_GET['project'] == $project->getName())) ? "selected" : false;
                    $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("{$project->getName()} (Update)", "{$url}ProjectProgressReport&project={$project->getName()}", $selected);
                }
            }
        }
        foreach($person->getProjects() as $project){
            if(strstr($project->getName(), "SSA2021") !== false ||
               strstr($project->getName(), "SSA2020") !== false ||
               strstr($project->getName(), "SSA2019") !== false ||
               strstr($project->getName(), "SSA2018") !== false){
                $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "SSAReport" && @$_GET['project'] == $project->getName())) ? "selected" : false;
                $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("{$project->getName()} Final Report", "{$url}SSAReport&project={$project->getName()}", $selected);
            }
        }
        if(count($hqps) > 0){
            $processedIFP = false;
            $processedIFP2016 = false;
            $processedIFP2017 = false;
            $processedIFP2018 = false;
            $processedIFP2019 = false;
            $processedIFP2020 = false;
            foreach($hqps as $hqp){
                if($hqp->isSubRole("IFP")){
                    $ifpDeleted = false;
                    $ifp2016 = false;
                    $ifp2017 = false;
                    $ifp2018 = false;
                    $ifp2019 = false;
                    $ifp2020 = false;
                    foreach($hqp->leadership() as $project){
                        $ifpDeleted = ($ifpDeleted || ($project->isDeleted() && (strstr($project->getName(), "IFP") !== false || strstr($project->getName(), "IFA") !== false)));
                        $ifp2016 = ($ifp2016 || strstr($project->getName(), "IFP2016") !== false || strstr($project->getName(), "IFA2016") !== false);
                        $ifp2017 = ($ifp2017 || strstr($project->getName(), "IFP2017") !== false || strstr($project->getName(), "IFA2017") !== false);
                        $ifp2018 = ($ifp2018 || strstr($project->getName(), "IFP2018") !== false || strstr($project->getName(), "IFA2018") !== false);
                        $ifp2019 = ($ifp2019 || strstr($project->getName(), "IFP2019") !== false || strstr($project->getName(), "IFA2019") !== false);
                        $ifp2020 = ($ifp2020 || strstr($project->getName(), "IFP2020") !== false || strstr($project->getName(), "IFA2020") !== false);
                    }
                    if(!$ifpDeleted){
                        if($ifp2020 && !$processedIFP2020){
                            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "IFP2020ProgressReport")) ? "selected" : false;
                            $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("IFP2020 Progress", "{$url}IFP2020ProgressReport", $selected);
                        }
                        if($ifp2019 && !$processedIFP2019){
                            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "IFP2019ProgressReport")) ? "selected" : false;
                            $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("IFP2019 Progress", "{$url}IFP2019ProgressReport", $selected);
                        }
                        if($ifp2018 && !$processedIFP2018){
                            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "IFP2018ProgressReport")) ? "selected" : false;
                            $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("IFP2018 Progress", "{$url}IFP2018ProgressReport", $selected);
                        }
                        if($ifp2017 && !$processedIFP2017){
                            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "IFP2017ProgressReport")) ? "selected" : false;
                            $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("IFP2017 Progress", "{$url}IFP2017ProgressReport", $selected);
                        }
                        if($ifp2016 && !$processedIFP2016){
                            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "IFP2016ProgressReport")) ? "selected" : false;
                            $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("IFP2016 Progress", "{$url}IFP2016ProgressReport", $selected);
                        }
                        if(!$ifp2020 && !$ifp2019 && !$ifp2018 && !$ifp2017 && !$ifp2016 && !$processedIFP){
                            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "IFPProgressReport")) ? "selected" : false;
                            $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("IFP Progress", "{$url}IFPProgressReport", $selected);
                        }
                    }
                    if($ifp2020 && !$processedIFP2020){
                        $processedIFP2020 = true;
                        $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "IFP2020FinalReport")) ? "selected" : false;
                        $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("IFP2020 Final", "{$url}IFP2020FinalReport", $selected);
                    }
                    if($ifp2019 && !$processedIFP2019){
                        $processedIFP2019 = true;
                        $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "IFP2019FinalReport")) ? "selected" : false;
                        $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("IFP2019 Final", "{$url}IFP2019FinalReport", $selected);
                    }
                    if($ifp2018 && !$processedIFP2018){
                        $processedIFP2018 = true;
                        $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "IFP2018FinalReport")) ? "selected" : false;
                        $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("IFP2018 Final", "{$url}IFP2018FinalReport", $selected);
                    }
                    if($ifp2017 && !$processedIFP2017){
                        $processedIFP2017 = true;
                        $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "IFP2017FinalReport")) ? "selected" : false;
                        $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("IFP2017 Final", "{$url}IFP2017FinalReport", $selected);
                    }
                    if($ifp2016 && !$processedIFP2016){
                        $processedIFP2016 = true;
                        $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "IFP2016FinalReport")) ? "selected" : false;
                        $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("IFP2016 Final", "{$url}IFP2016FinalReport", $selected);
                    }
                    if(!$ifp2020 && !$ifp2019 && !$ifp2018 && !$ifp2017 && !$ifp2016 && !$processedIFP){
                        $processedIFP = true;
                        $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "IFPFinalReport")) ? "selected" : false;
                        $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("IFP Final", "{$url}IFPFinalReport", $selected);
                    }
                }
                
            }
        }
        if(count($students) > 0){
            $processedIFP2020 = false;
            foreach($students as $student){
                if(!$processedIFP2020 && $student->isSubRole("IFP2021Applicant")){
                    $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "IFPApplication")) ? "selected" : false;
                    $tabs["Applications"]['subtabs'][] = TabUtils::createSubTab("IFP Application", "{$url}IFPApplication", $selected);
                    $processedIFP2020 = true;
                }
            }
        }
        if($person->isSubRole('IFP')){
            $ifpDeleted = false;
            $ifp2016 = false;
            $ifp2017 = false;
            $ifp2018 = false;
            $ifp2019 = false;
            $ifp2020 = false;
            foreach($person->leadership() as $project){
                $ifpDeleted = ($ifpDeleted || ($project->isDeleted() && (strstr($project->getName(), "IFP") !== false || strstr($project->getName(), "IFA") !== false)));
                $ifp2016 = ($ifp2016 || strstr($project->getName(), "IFP2016") !== false || strstr($project->getName(), "IFA2016") !== false);
                $ifp2017 = ($ifp2017 || strstr($project->getName(), "IFP2017") !== false || strstr($project->getName(), "IFA2017") !== false);
                $ifp2018 = ($ifp2018 || strstr($project->getName(), "IFP2018") !== false || strstr($project->getName(), "IFA2018") !== false);
                $ifp2019 = ($ifp2019 || strstr($project->getName(), "IFP2019") !== false || strstr($project->getName(), "IFA2019") !== false);
                $ifp2020 = ($ifp2020 || strstr($project->getName(), "IFP2020") !== false || strstr($project->getName(), "IFA2020") !== false);
            }
            if(!$ifpDeleted){
                if($ifp2020){
                    $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "IFP2020ProgressReport")) ? "selected" : false;
                    $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("IFP Progress", "{$url}IFP2020ProgressReport", $selected);
                }
                if($ifp2019){
                    $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "IFP2019ProgressReport")) ? "selected" : false;
                    $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("IFP Progress", "{$url}IFP2019ProgressReport", $selected);
                }
                if($ifp2018){
                    $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "IFP2018ProgressReport")) ? "selected" : false;
                    $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("IFP Progress", "{$url}IFP2018ProgressReport", $selected);
                }
                if($ifp2017){
                    $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "IFP2017ProgressReport")) ? "selected" : false;
                    $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("IFP Progress", "{$url}IFP2017ProgressReport", $selected);
                }
                if($ifp2016){
                    $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "IFP2016ProgressReport")) ? "selected" : false;
                    $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("IFP Progress", "{$url}IFP2016ProgressReport", $selected);
                }
                if(!$ifp2020 && !$ifp2019 && !$ifp2018 && !$ifp2017 && !$ifp2016){
                    $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "IFPProgressReport")) ? "selected" : false;
                    $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("IFP Progress", "{$url}IFPProgressReport", $selected);
                }
            }
            if($ifp2020){
                $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "IFP2020FinalReport")) ? "selected" : false;
                $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("IFP Final", "{$url}IFP2020FinalReport", $selected);
            }
            if($ifp2019){
                $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "IFP2019FinalReport")) ? "selected" : false;
                $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("IFP Final", "{$url}IFP2019FinalReport", $selected);
            }
            if($ifp2018){
                $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "IFP2018FinalReport")) ? "selected" : false;
                $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("IFP Final", "{$url}IFP2018FinalReport", $selected);
            }
            if($ifp2017){
                $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "IFP2017FinalReport")) ? "selected" : false;
                $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("IFP Final", "{$url}IFP2017FinalReport", $selected);
            }
            if($ifp2016){
                $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "IFP2016FinalReport")) ? "selected" : false;
                $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("IFP Final", "{$url}IFP2016FinalReport", $selected);
            }
            if(!$ifp2020 && !$ifp2019 && !$ifp2018 && !$ifp2017 && !$ifp2016){
                $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "IFPFinalReport")) ? "selected" : false;
                $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("IFP Final", "{$url}IFPFinalReport", $selected);
            }
        }
        // ECR
        if(count($person->getEvaluates("ECR-ALONE", 2022)) > 0){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "ECRAloneReview")) ? "selected" : false;
            $tabs["Reviews"]['subtabs'][] = TabUtils::createSubTab("ECR Review (CFN)", "{$url}ECRAloneReview", $selected);
        }
        if(count($person->getEvaluates("ECR-AGEWELL", 2022)) > 0){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "ECRAgewellReview")) ? "selected" : false;
            $tabs["Reviews"]['subtabs'][] = TabUtils::createSubTab("ECR Review (AGE-WELL/CFN)", "{$url}ECRAgewellReview", $selected);
        }
        if(count($person->getEvaluates("ECR-PERLEY", 2022)) > 0){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "ECRPerleyReview")) ? "selected" : false;
            $tabs["Reviews"]['subtabs'][] = TabUtils::createSubTab("ECR Review (Perley/CFN)", "{$url}ECRPerleyReview", $selected);
        }
        if(count($person->getEvaluates("ECR-SEPSIS", 2022)) > 0){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "ECRSepsisReview")) ? "selected" : false;
            $tabs["Reviews"]['subtabs'][] = TabUtils::createSubTab("ECR Review (Sepsis/CFN)", "{$url}ECRSepsisReview", $selected);
        }
        // IFP
        if(count($person->getEvaluates("IFP-ETC", 2020)) > 0){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "IFPReview")) ? "selected" : false;
            $tabs["Reviews"]['subtabs'][] = TabUtils::createSubTab("IFP Review (2020)", "{$url}IFPReview", $selected);
        }
        // KT
        if(count($person->getEvaluates("KT-EX", 2019)) > 0 || 
           count($person->getEvaluates("KT-KTC", 2019)) > 0 || 
           count($person->getEvaluates("KT-RMC", 2019)) > 0){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "KTReview2019")) ? "selected" : false;
            $tabs["Reviews"]['subtabs'][] = TabUtils::createSubTab("KT Review (2019)", "{$url}KTReview2019", $selected);
        }
        if(count($person->getEvaluates("KT_INTENT-EX", 2019)) > 0){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "KTIntentReview")) ? "selected" : false;
            $tabs["Reviews"]['subtabs'][] = TabUtils::createSubTab("KT Intent Review (2019)", "{$url}KTIntentReview", $selected);
        }
        if(count($person->getEvaluates("SHOW-EX", 2018)) > 0){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "SHOWReview")) ? "selected" : false;
            $tabs["Reviews"]['subtabs'][] = TabUtils::createSubTab("SHOW Review (2018)", "{$url}SHOWReview", $selected);
        }
        if(count($person->getEvaluates("CAT-SRC", 2018)) > 0 || count($person->getEvaluates("CAT-EX", 2018)) > 0 || count($person->getEvaluates("CAT-RMC", 2018)) > 0){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "Catalyst2018Review")) ? "selected" : false;
            $tabs["Reviews"]['subtabs'][] = TabUtils::createSubTab("CAT Review (2018)", "{$url}Catalyst2018Review", $selected);
        }
        if($person->isRoleAtLeast(STAFF)){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "ReviewReport2022")) ? "selected" : false;
            $tabs["Reviews"]['subtabs'][] = TabUtils::createSubTab("Reviews 2022", "{$url}ReviewReport2022", $selected);
            
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "ReviewReport2020")) ? "selected" : false;
            $tabs["Reviews"]['subtabs'][] = TabUtils::createSubTab("Reviews 2020", "{$url}ReviewReport2020", $selected);
            
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "ReviewReport2019")) ? "selected" : false;
            $tabs["Reviews"]['subtabs'][] = TabUtils::createSubTab("Reviews 2019", "{$url}ReviewReport2019", $selected);
            
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "ReviewReport2018")) ? "selected" : false;
            $tabs["Reviews"]['subtabs'][] = TabUtils::createSubTab("Reviews 2018", "{$url}ReviewReport2018", $selected);
            
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "ReviewReport2017")) ? "selected" : false;
            $tabs["Reviews"]['subtabs'][] = TabUtils::createSubTab("Reviews 2017", "{$url}ReviewReport2017", $selected);
        }
        /*if($person->isSubRole('FOCUS')){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "FocusStudy")) ? "selected" : false;
            $tabs["Surveys"]['subtabs'][] = TabUtils::createSubTab("FOCUS", "{$url}FocusStudy", $selected);
        }*/
        if($person->isRoleAtLeast(ADMIN) || $person->getName() == "Jeanette.Prorok"){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "FocusResults")) ? "selected" : false;
            $tabs["Surveys"]['subtabs'][] = TabUtils::createSubTab("FOCUS Results", "{$url}FocusResults", $selected);
        }
        return true;
    }
    
    static function createToolboxLinks(&$toolbox){
        global $wgServer, $wgScriptPath, $config;

        return true;
    }
}

?>
