<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['Report'] = 'Report'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['Report'] = $dir . 'Report.i18n.php';
$wgSpecialPageGroups['Report'] = 'reporting-tools';

$wgHooks['TopLevelTabs'][] = 'Report::createTab';
$wgHooks['SubLevelTabs'][] = 'Report::createSubTabs';
$wgHooks['ToolboxLinks'][] = 'Report::createToolboxLinks';

class Report extends AbstractReport{
    
    function __construct(){
        global $config;
        $report = @$_GET['report'];
        parent::__construct(dirname(__FILE__)."/../../ReportXML/{$config->getValue('networkName')}/$report.xml", -1, false, false);
    }

    static function createTab(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        $tabs["Surveys"] = TabUtils::createTab("Intake Survey");
        $tabs["Modules"] = TabUtils::createTab("Education Modules");
        return true;
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        $person = Person::newFromWgUser();
        $url = "$wgServer$wgScriptPath/index.php/Special:Report?report=";
        if($person->isLoggedIn()){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "IntakeSurvey")) ? "selected" : false;
            $tabs["Surveys"]['subtabs'][] = TabUtils::createSubTab("Intake Survey", "{$url}IntakeSurvey", $selected);
        }
        if($person->isLoggedIn()){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "EducationModules/IngredientsForChange")) ? "selected" : false;
            $tabs["Modules"]['subtabs'][] = TabUtils::createSubTab("Ingredients for Change", "{$url}EducationModules/IngredientsForChange", $selected);
        }
        return true;
    }
    
    static function createToolboxLinks(&$toolbox){
        global $wgServer, $wgScriptPath, $config;

        return true;
    }
}

?>
