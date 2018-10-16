<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['Report'] = 'Report'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['Report'] = $dir . 'Report.i18n.php';
$wgSpecialPageGroups['Report'] = 'reporting-tools';

$wgHooks['TopLevelTabs'][] = 'Report::createTab';
$wgHooks['SubLevelTabs'][] = 'Report::createSubTabs';

require_once("GraduateStudents.php");

class Report extends AbstractReport{
    
    function Report(){
        global $config;
        $report = @$_GET['report'];
        $topProjectOnly = false;
        $this->AbstractReport(dirname(__FILE__)."/../../ReportXML/{$config->getValue('networkName')}/$report.xml", -1, false, $topProjectOnly);
    }

    static function createTab(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        if($wgUser->isLoggedIn()){
            $tabs["Reports"] = TabUtils::createTab("My Annual Report");
            $tabs["CV"] = TabUtils::createTab("My QA CV");
            $tabs["Chair"] = TabUtils::createTab("Chair");
            $tabs["Dean"] = TabUtils::createTab("Dean");
            $tabs["FEC"] = TabUtils::createTab("FEC");
        }
        return true;
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle;
        $person = Person::newFromWgUser();
        $url = "$wgServer$wgScriptPath/index.php/Special:Report?report=";
        if($person->isRole(NI)){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "FEC")) ? "selected" : false;
            $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("Annual Report", "{$url}FEC", $selected);
            
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "QACV")) ? "selected" : false;
            $tabs["CV"]['subtabs'][] = TabUtils::createSubTab("QA CV", "{$url}QACV", $selected);
        }
        if($person->isRole(ISAC) || $person->isRole(IAC)){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "ChairTable")) ? "selected" : false;
            $tabs["Chair"]['subtabs'][] = TabUtils::createSubTab("Annual Reports", "{$url}ChairTable", $selected);
        }
        if($person->isRole(DEAN) || $person->isRole(DEANEA)){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "ChairTable")) ? "selected" : false;
            $tabs["Dean"]['subtabs'][] = TabUtils::createSubTab("Annual Reports", "{$url}ChairTable", $selected);
        }
        if($person->isRole(DEAN) || $person->isRole(VDEAN)){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "FECTable")) ? "selected" : false;
            $tabs["FEC"]['subtabs'][] = TabUtils::createSubTab("Annual Reports", "{$url}FECTable", $selected);
        }
        return true;
    }
}

?>
