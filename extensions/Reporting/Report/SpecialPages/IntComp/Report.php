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
        if(isset($_GET['project'])){
            $topProjectOnly = true;
        }
        $this->AbstractReport(dirname(__FILE__)."/../../ReportXML/{$config->getValue('networkName')}/$report.xml", -1, false, $topProjectOnly);
    }

    static function createTab(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        $tabs["Reports"] = TabUtils::createTab("My Reports");
        $tabs["Proposals"] = TabUtils::createTab("Huawei");
        $tabs["Awards"] = TabUtils::createTab("My Awards");
        $tabs["Reviews"] = TabUtils::createTab("My Reviews");
        
        return true;
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        $person = Person::newFromWgUser();
        $url = "$wgServer$wgScriptPath/index.php/Special:Report?report=";
        
        if($person->isLoggedIn()){
            /*$selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "HuaweiFall2019")) ? "selected" : false;
            $tabs["Proposals"]['subtabs'][] = TabUtils::createSubTab("JIC (Fall 2019)", "{$url}Huawei", $selected);*/
            
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "LOIFall2019")) ? "selected" : false;
            $tabs["Proposals"]['subtabs'][] = TabUtils::createSubTab("JIC LOI (Fall 2019)", "{$url}LOI", $selected);
        }
        
        return true;
    }
    
    static function createToolboxLinks(&$toolbox){
        global $wgServer, $wgScriptPath, $config;

        return true;
    }
}

?>
