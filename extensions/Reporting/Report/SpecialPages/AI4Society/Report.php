<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['Report'] = 'Report'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['Report'] = $dir . 'Report.i18n.php';
$wgSpecialPageGroups['Report'] = 'reporting-tools';

require_once("ApplicationsTable.php");

$wgHooks['TopLevelTabs'][] = 'Report::createTab';
$wgHooks['SubLevelTabs'][] = 'Report::createSubTabs';
$wgHooks['ToolboxHeaders'][] = 'Report::createToolboxHeaders';
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
        $tabs["Applications"] = TabUtils::createTab("My Proposals");
        return true;
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        $person = Person::newFromWgUser();
        $url = "$wgServer$wgScriptPath/index.php/Special:Report?report=";
        if($person->isLoggedIn()){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "ProjectProposal")) ? "selected" : false;
            $tabs["Applications"]['subtabs'][] = TabUtils::createSubTab("Project Proposal", "{$url}ProjectProposal", $selected);
        }
        if($person->isLoggedIn()){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "Telus5G")) ? "selected" : false;
            $tabs["Applications"]['subtabs'][] = TabUtils::createSubTab("Telus 5G", "{$url}Telus5G", $selected);
        }
        return true;
    }
    
    static function createToolboxHeaders(&$toolbox){
        global $wgServer, $wgScriptPath, $config;
        $toolbox['AcademicPrograms'] = TabUtils::createToolboxHeader("Academic Programs");
        return true;
    }
    
    static function createToolboxLinks(&$toolbox){
        global $wgServer, $wgScriptPath, $config;
        $other = $toolbox['Other'];
        unset($toolbox['Other']);
        $toolbox['AcademicPrograms']['links'][] = TabUtils::createToolboxLink("Course Catalogue", "$wgServer$wgScriptPath/index.php/Courses");
        $toolbox['Other'] = $other;
        return true;
    }
}

?>
