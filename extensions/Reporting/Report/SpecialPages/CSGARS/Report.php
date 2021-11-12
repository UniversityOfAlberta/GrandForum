<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['Report'] = 'Report'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['Report'] = $dir . 'Report.i18n.php';
$wgSpecialPageGroups['Report'] = 'reporting-tools';

$wgHooks['TopLevelTabs'][] = 'Report::createTab';
$wgHooks['SubLevelTabs'][] = 'Report::createSubTabs';
$wgHooks['ToolboxLinks'][] = 'Report::createToolboxLinks';
$wgHooks['BeforePageDisplay'][] = 'Report::redirect';

require_once("Sops/Sops.php");
require_once("ReportStatusTable.php");

class Report extends AbstractReport{
    
    function Report(){
        global $config;
        $report = @$_GET['report'];
        $topProjectOnly = false;
        $this->AbstractReport(dirname(__FILE__)."/../../ReportXML/{$config->getValue('networkName')}/$report.xml", -1, false, $topProjectOnly);
    }

    function redirect($out, $text) {
        global $wgTitle, $wgServer, $wgScriptPath;
        $me = Person::newFromWgUser();
        if($wgTitle->getText() == "FAQs") {
            return true;
        }
        if(@$_GET['report'] == "CSForm" && !($me->getSopPdfUrl() == false)){
            redirect($me->getUrl());
        }
        elseif(($wgTitle->getText() == "Main Page" || $wgTitle->getText() == "UserLogin") && $me->isRole(CI) && !($me->getSopPdfUrl() == false) && $_GET['action'] != "viewNotifications"){
            redirect($me->getUrl());
        }
        elseif(($wgTitle->getText() == "Main Page"|| $wgTitle->getText() == "UserLogin") && $me->isRole(HQP) && $_GET['action'] != "viewNotifications"){
            redirect($me->getUrl());
        }
        else if(($wgTitle->getText() == "Main Page" || $wgTitle->getText() == "UserLogin") && $me->isRole(CI)  && $_GET['action'] != "viewNotifications"){
            redirect("$wgServer$wgScriptPath/index.php/Special:Report?report=CSForm");
        }
    } 

    static function createTab(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        $tabs["Reports"] = TabUtils::createTab("Letter of Intent");
        
        return true;
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        $person = Person::newFromWgUser();
        $url = "$wgServer$wgScriptPath/index.php/Special:Report?report=";

        if($person->isRole(CI)){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "CSForm")) ? "selected" : false;
            $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("Letter of Intent", "{$url}CSForm", $selected);
        }
        
        return true;
    }
    
    static function createToolboxLinks(&$toolbox){
        global $wgServer, $wgScriptPath, $config;

        return true;
    }
}

?>
