<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['Report'] = 'Report'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['Report'] = $dir . 'Report.i18n.php';
$wgSpecialPageGroups['Report'] = 'reporting-tools';

$wgHooks['TopLevelTabs'][] = 'Report::createTab';
$wgHooks['SubLevelTabs'][] = 'Report::createSubTabs';
$wgHooks['BeforePageDisplay'][] = 'Report::addFooter';
$wgHooks['BeforePageDisplay'][] = 'Report::disableSubTabs';
$wgHooks['PersonExtra'][] = 'Report::personExtra';

require_once("AVOIDDashboard.php");
require_once("FrailtyReport.php");
require_once("ProgressReport.php");
require_once("InPersonAssessment.php");
require_once("InPersonFollowup.php");
require_once("AdminDataCollection.php");
require_once("AdminUsageStats.php");
require_once("FitbitStats.php");
require_once("Descriptors.php");
require_once("IntakeSummary.php");
require_once("ThreeMonthSummary.php");
require_once("SixMonthSummary.php");
require_once("NineMonthSummary.php");
require_once("TwelveMonthSummary.php");
require_once("EducationResources/EducationResources.php");
require_once("Programs/Programs.php");
require_once("ActionPlan/ActionPlan.php");
require_once("PharmacyMap/PharmacyMap.php");
require_once("ClipboardList/ClipboardList.php");
require_once("AskAnExpert/AskAnExpert.php");
require_once("UsageVisualizations.php");
require_once("HowDidYouHearOfUs.php");

class Report extends AbstractReport{
    
    function __construct(){
        global $config;
        $report = @$_GET['report'];
        parent::__construct(dirname(__FILE__)."/../../ReportXML/{$config->getValue('networkName')}/$report.xml", -1, false, false);
    }

    static function createTab(&$tabs){
        global $wgServer, $wgScriptPath, $wgTitle;
        $tabs["Surveys"] = TabUtils::createTab("<en>Healthy Aging Assessment</en><fr>Évaluation du vieillissement sain</fr>");
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
                $tabs["Surveys"]['subtabs'][] = TabUtils::createSubTab("<en>Healthy Aging Assessment</en><fr>Évaluation du vieillissement sain</fr>", "{$url}IntakeSurvey&section={$section}", $selected);
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
            
            #achievementContainer {
                position: fixed;
                top: 15px;
                right: 20px;
                width: 25em;
                font-size: 1.25em;
                user-select: none;
                pointer-events: none;
                min-height: 5em;
                z-index: 100000;
            }
            
            #achievement {
                background: #222222;
                background-image: url('{$wgServer}{$wgScriptPath}/skins/goldstar.png');
                background-repeat: no-repeat;
                background-size: 4em 4em;
                background-position: 0.5em 0.5em; 
                color: white;
                min-height: 4em;
                padding: 0.5em 1em;
                padding-left: 5em;
                border-radius: 0.75em;
                box-shadow: 3px 3px 6px rgba(0,0,0,0.5);
                transition: filter 0.25s, opacity 0.25s;
            }
            
            #achievement.hover {
                opacity: 0.25 !important;
                filter: blur(0.25em);
            }
            
            .top-nav .top-nav-element a {
                font-size: 1.5em;
            }
            
            #header {
                height: 50px;
            }
            
            #header ul a {
                height: 56px;
                line-height: 56px;
            }
            
            #header ul a:hover, #header ul a:focus {
                height: 53px;
                line-height: 50px;
            }
            
            #header li.selected a {
                height: 53px;
                line-height: 50px;
            }
            
            #allTabs {
                height: 52px;
                line-height: 49px;
            }
            
            #allTabsDropdown {
                top: 102px;
            }
            
            div#submenu {
                top: 102px;
            }
            
            #mobileMenu {
                top: 102px !important;
            }
            
            @media only screen and (min-width: 1024px) {
                #contactUs, #reportIssue {
                    margin-top: 13px !important;
                }
            }
            
            @media only screen and (max-width: 767px) {
                #achievementContainer {
                    right: 20px !important;
                    left: 20px !important;
                    width: auto !important;
                }
                
                #achievement {
                    font-size: 0.85em;
                    line-height: 1.25em;
                }
            }
        </style>");
        $wgOut->addScript("<script src='{$wgServer}{$wgScriptPath}/extensions/Reporting/Report/SpecialPages/AVOID/avoid.js?".filemtime('extensions/Reporting/Report/SpecialPages/AVOID/avoid.js')."'></script>");
        $wgOut->addHTML("<audio id='ding' preload='auto'><source src='{$wgServer}{$wgScriptPath}/skins/ding.mp3' type='audio/mpeg' /></audio>");
        $wgOut->addHTML("<div id='achievementContainer' style='opacity:0; right: -5em;'>
                            <div id='achievement'><b style='display:inline-block;font-size:1.25em; margin-top: 0.5em; margin-bottom: 0.2em;'><span id='achievementPoints'>X</span> points</b><br /><div id='achievementText' style='margin-bottom: 0.5em;'>Lorem Ipsum</div></div>
                         </div>");
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
    
    static function personExtra($person, &$extra){
        $extra['postal'] = IntakeSummary::getBlobData("AVOID_Questions_tab0", "POSTAL", $person, YEAR, "RP_AVOID");
        return true;
    }
    
}

?>
