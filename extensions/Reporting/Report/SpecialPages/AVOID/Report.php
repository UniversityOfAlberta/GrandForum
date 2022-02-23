<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['Report'] = 'Report'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['Report'] = $dir . 'Report.i18n.php';
$wgSpecialPageGroups['Report'] = 'reporting-tools';

$wgHooks['TopLevelTabs'][] = 'Report::createTab';
$wgHooks['SubLevelTabs'][] = 'Report::createSubTabs';
$wgHooks['BeforePageDisplay'][] = 'Report::disableSubTabs';

require_once("AVOIDDashboard.php");
require_once("EducationModules/EducationModules.php");
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
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "IntakeSurvey")) ? "selected" : false;
            $tabs["Surveys"]['subtabs'][] = TabUtils::createSubTab("Healthy Aging Assessment", "{$url}IntakeSurvey", $selected);
        }
        return true;
    }
    
    static function disableSubTabs($wgOut, $skin){
        $wgOut->addScript("<style>
            #submenu {
                display: none;
            }
            
            #bodyContent {
                top: 90px;
            }
        </style>");
    }
    
}

?>
