<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['Report'] = 'Report'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['Report'] = $dir . 'Report.i18n.php';
$wgSpecialPageGroups['Report'] = 'reporting-tools';

$wgHooks['TopLevelTabs'][] = 'Report::createTab';
$wgHooks['SubLevelTabs'][] = 'Report::createSubTabs';
$wgHooks['BeforePageDisplay'][] = 'Report::addFooter';
$wgHooks['BeforePageDisplay'][] = 'Report::disableSubTabs';

require_once("AVOIDDashboard.php");
require_once("EducationResources/EducationResources.php");
require_once("Programs/Programs.php");

class Report extends AbstractReport{
    
    function __construct(){
        global $config;
        $report = @$_GET['report'];
        parent::__construct(dirname(__FILE__)."/../../ReportXML/{$config->getValue('networkName')}/$report.xml", -1, false, false);
    }

    static function createTab(&$tabs){
        global $wgServer, $wgScriptPath, $wgTitle;
        $tabs["Surveys"] = TabUtils::createTab("Healthy Aging Assessment");
        return true;
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgTitle;
        $person = Person::newFromWgUser();
        $url = "$wgServer$wgScriptPath/index.php/Special:Report?report=";
        if($person->isLoggedIn()){
            if(!AVOIDDashboard::hasSubmittedSurvey()){
                $section = AVOIDDashboard::getNextIncompleteSection();
                $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "IntakeSurvey")) ? "selected" : false;
                $tabs["Surveys"]['subtabs'][] = TabUtils::createSubTab("Healthy Aging Assessment", "{$url}IntakeSurvey&section={$section}", $selected);
            }
        }
        return true;
    }
    
    static function addFooter($wgOut, $skin){
        global $wgServer, $wgScriptPath;
        $wgOut->addScript("<style>
            #avoidButtons {
                margin-top: 5px;
                text-align: center;
                width: 100%;
            }
            
            #footer {
                display: none;
            }
        </style>");
        $wgOut->addScript("<script src='{$wgServer}{$wgScriptPath}/extensions/Reporting/Report/SpecialPages/AVOID/avoid.js'></script>");
    }
    
    static function disableSubTabs($wgOut, $skin){
        $wgOut->addScript("<style>
            #submenu {
                display: none;
            }
            
            #bodyContent {
                top: 90px;
            }
            
            #sideToggle {
                display: none;
            }
        </style>");
    }
    
}

?>
