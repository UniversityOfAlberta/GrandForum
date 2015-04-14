<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['ReportSurvey'] = 'ReportSurvey'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['ReportSurvey'] = $dir . 'ReportSurvey.i18n.php';
$wgSpecialPageGroups['ReportSurvey'] = 'reporting-tools';

$wgHooks['TopLevelTabs'][] = 'ReportSurvey::createTab';
$wgHooks['SubLevelTabs'][] = 'ReportSurvey::createSubTabs';

class ReportSurvey extends AbstractReport{
    
    function ReportSurvey(){
        global $config;
        $report = @$_GET['report'];
        $this->AbstractReport(dirname(__FILE__)."/../../ReportXML/{$config->getValue('networkName')}/$report.xml", -1, false, false);\
        SpecialPage::__construct("ReportSurvey", HQP.'+', true);
        $this->showInstructions = false;
    }

    static function createTab(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle;
        $tabs["Surveys"] = TabUtils::createTab("Surveys");
        return true;
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle;
        $person = Person::newFromWgUser();
        if($person->isRoleAtLeast(MANAGER)){
            $selected = @($wgTitle->getText() == "ReportSurvey" && ($_GET['report'] == "MindTheGapManager")) ? "selected" : "";
            $tabs["Surveys"]['subtabs'][] = TabUtils::createSubTab("Mind The Gap (Manager)",
                                                                   "$wgServer$wgScriptPath/index.php/Special:ReportSurvey?report=MindTheGapManager",
                                                                   $selected);
        }
        if($person->isRoleAtLeast(HQP)){
            $selected = @($wgTitle->getText() == "ReportSurvey" && ($_GET['report'] == "MindTheGap")) ? "selected" : "";
            $tabs["Surveys"]['subtabs'][] = TabUtils::createSubTab("Mind The Gap", 
                                                                   "$wgServer$wgScriptPath/index.php/Special:ReportSurvey?report=MindTheGap", 
                                                                   $selected);
        }
        return true;
    }
}

?>
