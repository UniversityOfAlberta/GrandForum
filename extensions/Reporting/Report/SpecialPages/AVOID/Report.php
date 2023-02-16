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
require_once("FrailtyReport.php");
require_once("ProgressReport.php");
require_once("InPersonAssessment.php");
require_once("InPersonFollowup.php");
require_once("AdminDataCollection.php");
require_once("AdminUsageStats.php");
require_once("IntakeSummary.php");
require_once("Descriptors.php");
require_once("ThreeMonthSummary.php");
require_once("SixMonthSummary.php");
require_once("EducationResources/EducationResources.php");
require_once("Programs/Programs.php");
require_once("ActionPlan/ActionPlan.php");
require_once("PharmacyMap/PharmacyMap.php");
require_once("ClipboardList/ClipboardList.php");
require_once("AskAnExpert/AskAnExpert.php");
require_once("UsageVisualizations.php");

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
        $me = Person::newFromWgUser();
        $wgOut->addScript("<style>
            #avoidButtons {
                margin-top: 15px;
                text-align: center;
                width: 100%;
            }
            
            #footer {
                display: none;
            }
        </style>");
        $wgOut->addScript("<script src='{$wgServer}{$wgScriptPath}/extensions/Reporting/Report/SpecialPages/AVOID/avoid.js?".filemtime('extensions/Reporting/Report/SpecialPages/AVOID/avoid.js')."'></script>");
        $wgOut->addHTML("<div title='Become a Member' style='display:none;' id='becomeMemberDialog'>
                            <div id='memberMessages'></div>
                            <p>After Completing a Healthy Aging Assessment, Members will Receive:</p>
                            <ul>
                                <li>A Personalized Report with Recommendations</li>
                                <li>Enhanced Behavioural Support</li>
                                <li>Ongoing Health Monitoring</li>
                                <li>Opportunities for In-Person Assessments</li>
                                <li>Access to Peer Coaching</li>
                            </ul>
                            <a id='becomeMember' class='program-button' href='#'>Yes, I want to be a member</a>
                        </div>");
        if($me->isLoggedIn()){
            $wgOut->addScript("<script type='text/javascript'>
                $(document).ready(function(){
                    var pageDC = new DataCollection();
                    pageDC.init(me.get('id'), document.URL.replace(wgServer, '').replace(wgScriptPath, '').replace('/index.php/', ''));
                    pageDC.timer('time');
                    pageDC.increment('hits');
                    
                    var loggedInDC = new DataCollection();
                    loggedInDC.init(me.get('id'), 'loggedin');
                    loggedInDC.append('log', new Date().toISOString().slice(0, 10), true);
                });
            </script>");
        }
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
