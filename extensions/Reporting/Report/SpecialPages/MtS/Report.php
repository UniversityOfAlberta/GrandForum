<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['Report'] = 'Report'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['Report'] = $dir . 'Report.i18n.php';
$wgSpecialPageGroups['Report'] = 'reporting-tools';

require_once("ApplicationsTable.php");

$wgHooks['TopLevelTabs'][] = 'Report::createTab';
$wgHooks['SubLevelTabs'][] = 'Report::createSubTabs';
$wgHooks['ToolboxLinks'][] = 'Report::createToolboxLinks';

class Report extends AbstractReport{
    
    function __construct(){
        global $config;
        $report = @$_GET['report'];
        $topProjectOnly = false;
        parent::__construct(dirname(__FILE__)."/../../ReportXML/{$config->getValue('networkName')}/$report.xml", -1, false, $topProjectOnly);
    }

    static function createTab(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        $tabs["Reports"] = TabUtils::createTab("My Reports");
        $tabs["Applications"] = TabUtils::createTab("My Applications");
        return true;
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        $person = Person::newFromWgUser();
        if(!$person->isLoggedIn()){
            return true;
        }
        $url = "$wgServer$wgScriptPath/index.php/Special:Report?report=";

        foreach($person->getProjects(true) as $project){
            if($person->isRole(PL, $project) || 
               $person->isRole(PA, $project) || 
               $person->isRole('RP', $project) ||
               $person->isRoleDuring(PL, $project->getStartDate(), $project->getEndDate(), $project) || 
               $person->isRoleDuring(PA, $project->getStartDate(), $project->getEndDate(), $project) || 
               $person->isRoleDuring('RP', $project->getStartDate(), $project->getEndDate(), $project)){
                $date_diff = date_diff(date_create(date('Y-m-d')), date_create($project->getEndDate()), false);
                if(intval($date_diff->format('%R%a')) <= 60){
                    $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "ProjectCompletionReport") && isset($_GET['project']) && $_GET['project'] == $project->getName()) ? "selected" : false;
                    $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("{$project->getName()} (Completion)", "{$url}ProjectCompletionReport&project={$project->getName()}", $selected);
                }
                $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "ProjectProgressReport") && isset($_GET['project']) && $_GET['project'] == $project->getName()) ? "selected" : false;
                $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("{$project->getName()} (Progress)", "{$url}ProjectProgressReport&project={$project->getName()}", $selected);

                $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "ProjectImpactReport") && isset($_GET['project']) && $_GET['project'] == $project->getName()) ? "selected" : false;
                $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("{$project->getName()} (Impact)", "{$url}ProjectImpactReport&project={$project->getName()}", $selected);
            }
        }
        
        if($person->isRoleAtLeast(INACTIVE) ||
           $person->isRoleAtLeast(INACTIVE.'-Candidate')){
            //$selected = @($wgTitle->getText() == "Report" && $_GET['report'] == "OpenCall2022") ? "selected" : false;
            //$tabs["Applications"]['subtabs'][] = TabUtils::createSubTab("Open Call 2022 (EN)", "{$url}OpenCall2022", $selected);
            
            //$selected = @($wgTitle->getText() == "Report" && $_GET['report'] == "OpenCall2022FR") ? "selected" : false;
            //$tabs["Applications"]['subtabs'][] = TabUtils::createSubTab("Open Call 2022 (FR)", "{$url}OpenCall2022FR", $selected);
           
            //$selected = @($wgTitle->getText() == "Report" && $_GET['report'] == "OpenRound2") ? "selected" : false;
            //$tabs["Applications"]['subtabs'][] = TabUtils::createSubTab("Open Call Round 2", "{$url}OpenRound2", $selected);
            
            //$selected = @($wgTitle->getText() == "Report" && $_GET['report'] == "DataTechnologyApplication") ? "selected" : false;
            //$tabs["Applications"]['subtabs'][] = TabUtils::createSubTab("Data Tech Call", "{$url}DataTechnologyApplication", $selected);
        }
        
        return true;
    }
    
    static function createToolboxLinks(&$toolbox){
        global $wgServer, $wgScriptPath, $config;
        return true;
    }
}

?>
