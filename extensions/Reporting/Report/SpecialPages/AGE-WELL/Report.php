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
        $tabs["Plans"] = TabUtils::createTab("CC Activity Plans");
        $tabs["Applications"] = TabUtils::createTab("HQP Application");
        return true;
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        $person = Person::newFromWgUser();
        $url = "$wgServer$wgScriptPath/index.php/Special:Report?report=";
        
        if($person->isRole(HQP."-Candidate")){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "HQPApplication")) ? "selected" : false;
            $tabs["Applications"]['subtabs'][] = TabUtils::createSubTab("HQP Application", "{$url}HQPApplication", $selected);
        }
        if($person->isRole(PL)){
            foreach($person->leadership() as $project){
                $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "CCPlanning")) ? "selected" : false;
                $tabs["Plans"]['subtabs'][] = TabUtils::createSubTab("{$project->getName()}", "{$url}CCPlanning&project={$project->getName()}", $selected);
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
