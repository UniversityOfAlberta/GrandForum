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
        $this->AbstractReport(dirname(__FILE__)."/../../ReportXML/{$config->getValue('networkName')}/$report.xml", -1, false, $topProjectOnly);
    }

    static function createTab(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        $tabs["Reports"] = TabUtils::createTab("My Reports");
        $tabs["Proposals"] = TabUtils::createTab("Huawei");
        $tabs["Awards"] = TabUtils::createTab("My Awards");
        $tabs["Reviews"] = TabUtils::createTab("My Reviews");
        $tabs["PCR"] = TabUtils::createTab("PCR");
        
        return true;
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        $person = Person::newFromWgUser();
        $url = "$wgServer$wgScriptPath/index.php/Special:Report?report=";
        
        if($person->isLoggedIn()){
            $projectId = 0;
            do{
                $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "LOI2021") && ($_GET['project'] == $projectId || (!isset($_GET['project']) && $projectId == 0))) ? "selected" : false;
                $tabName = ($projectId > 0) ? "[".($projectId+1)."]" : "JIC LOI (Spring 2021)&nbsp;&nbsp;&nbsp;[".($projectId+1)."]";
                $tabs["Proposals"]['subtabs'][] = TabUtils::createSubTab($tabName, "{$url}LOI2021&project=$projectId", $selected);
                
                $report = new DummyReport("LOI2021", $person, ++$projectId, YEAR, true);
            } while($report->hasStarted());

            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "LOI2021") && ($_GET['project'] == $projectId)) ? "selected" : false;
            $tabs["Proposals"]['subtabs'][] = TabUtils::createSubTab("[+]", "{$url}LOI2021&project=$projectId", $selected);
            
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "HuaweiFall2019")) ? "selected" : false;
            $leadership = $person->leadership();
            
            $tabs["Proposals"]['subtabs'][] = TabUtils::createSubTab("JIC (Spring 2019)", "{$url}HuaweiFall2019", $selected);

            if($person->isRole("UAHJIC")){
                $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "HuaweiReview")) ? "selected" : false;
                $tabs["Reviews"]['subtabs'][] = TabUtils::createSubTab("JIC Review", "{$url}HuaweiReview", $selected);
                
                $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "ProgressReview")) ? "selected" : false;
                $tabs["Reviews"]['subtabs'][] = TabUtils::createSubTab("Progress Review", "{$url}ProgressReview", $selected);
                
                $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "PCRReview")) ? "selected" : false;
                $tabs["Reviews"]['subtabs'][] = TabUtils::createSubTab("PCR Review", "{$url}PCRReview", $selected);
            }
            if($person->isRole("UAHJIC") || $person->isRole(PL)){
                // PCR
                $projectId = 0;
                do{
                    $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "PCR") && ($_GET['project'] == $projectId || (!isset($_GET['project']) && $projectId == 0))) ? "selected" : false;
                    $tabName = "[".($projectId+1)."]";
                    $tabs["PCR"]['subtabs'][] = TabUtils::createSubTab($tabName, "{$url}PCR&project=$projectId", $selected);
                    
                    $report = new DummyReport("PCR", $person, ++$projectId, YEAR, true);
                } while($report->hasStarted());

                $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "PCR") && ($_GET['project'] == $projectId)) ? "selected" : false;
                $tabs["PCR"]['subtabs'] = array_reverse($tabs["PCR"]['subtabs']);
                $tabs["PCR"]['subtabs'][] = TabUtils::createSubTab("[+]", "{$url}PCR&project=$projectId", $selected);
            }
            
            if(count($leadership) > 0){
                foreach($leadership as $project){
                    $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "ProgressReport" && @$_GET['project'] == $project->getName())) ? "selected" : false;
                    $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("{$project->getName()}", "{$url}ProgressReport&project={$project->getName()}", $selected);
                }
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
