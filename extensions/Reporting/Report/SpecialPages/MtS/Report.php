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
    
    function Report(){
        global $config;
        $report = @$_GET['report'];
        $topProjectOnly = false;
        $this->AbstractReport(dirname(__FILE__)."/../../ReportXML/{$config->getValue('networkName')}/$report.xml", -1, false, $topProjectOnly);
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
        $url = "$wgServer$wgScriptPath/index.php/Special:Report?report=";

        foreach($person->getProjects() as $project){
            if($person->leadershipOf($project) || $person->isRole(PA, $project)){
                $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "ProjectImpactReport") && isset($_GET['project']) && $_GET['project'] == $project->getName()) ? "selected" : false;
                $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("{$project->getName()}", "{$url}ProjectImpactReport&project={$project->getName()}", $selected);
            }
        }
        
        /*if($person->isRoleAtLeast(INACTIVE) ||
           $person->isRoleAtLeast(INACTIVE.'-Candidate')){
            $selected = @($wgTitle->getText() == "Report" && $_GET['report'] == "OpenRound2") ? "selected" : false;
            $tabs["Applications"]['subtabs'][] = TabUtils::createSubTab("Open Round2", "{$url}OpenRound2", $selected);
            
            $selected = @($wgTitle->getText() == "Report" && $_GET['report'] == "DataTechnologyApplication") ? "selected" : false;
            $tabs["Applications"]['subtabs'][] = TabUtils::createSubTab("DataTech", "{$url}DataTechnologyApplication", $selected);
        }*/
        
        return true;
    }
    
    static function createToolboxLinks(&$toolbox){
        global $wgServer, $wgScriptPath, $config;
        return true;
    }
}

?>
