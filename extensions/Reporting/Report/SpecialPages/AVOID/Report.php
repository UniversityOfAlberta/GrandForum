<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['Report'] = 'Report'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['Report'] = $dir . 'Report.i18n.php';
$wgSpecialPageGroups['Report'] = 'reporting-tools';

$wgHooks['TopLevelTabs'][] = 'Report::createTab';
$wgHooks['SubLevelTabs'][] = 'Report::createSubTabs';

class Report extends AbstractReport{
    
    function __construct(){
        global $config;
        $report = @$_GET['report'];
        parent::__construct(dirname(__FILE__)."/../../ReportXML/{$config->getValue('networkName')}/$report.xml", -1, false, false);
    }

    static function createTab(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        $tabs["Surveys"] = TabUtils::createTab("Healthy Aging Assessment");
        $tabs["Modules"] = TabUtils::createTab("AVOID Education");
        $tabs["Programs"] = TabUtils::createTab("Programs");
        return true;
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        $person = Person::newFromWgUser();
        $url = "$wgServer$wgScriptPath/index.php/Special:Report?report=";
        if($person->isLoggedIn()){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "IntakeSurvey")) ? "selected" : false;
            $tabs["Surveys"]['subtabs'][] = TabUtils::createSubTab("Healthy Aging Assessment", "{$url}IntakeSurvey", $selected);
        }
        if($person->isLoggedIn()){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "EducationModules/Activity")) ? "selected" : false;
            $tabs["Modules"]['subtabs'][] = TabUtils::createSubTab("Activity", "{$url}EducationModules/Activity", $selected);
            
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "EducationModules/IngredientsForChange")) ? "selected" : false;
            $tabs["Modules"]['subtabs'][] = TabUtils::createSubTab("Ingredients for Change", "{$url}EducationModules/IngredientsForChange", $selected);
            
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "EducationModules/OptimizeMedication")) ? "selected" : false;
            $tabs["Modules"]['subtabs'][] = TabUtils::createSubTab("Optimize Medication", "{$url}EducationModules/OptimizeMedication", $selected);
            
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "EducationModules/Vaccination")) ? "selected" : false;
            $tabs["Modules"]['subtabs'][] = TabUtils::createSubTab("Vaccination", "{$url}EducationModules/Vaccination", $selected);
        }
        if($person->isLoggedIn()){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "Programs/PeerCoaching")) ? "selected" : false;
            $tabs["Programs"]['subtabs'][] = TabUtils::createSubTab("Get Support from your peers", "{$url}Programs/PeerCoaching", $selected);
        }
        return true;
    }
    
}

?>
