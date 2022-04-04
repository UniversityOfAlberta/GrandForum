<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['FrailtyReport'] = 'FrailtyReport'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['FrailtyReport'] = $dir . 'FrailtyReport.i18n.php';
$wgSpecialPageGroups['FrailtyReport'] = 'reporting-tools';

function runFrailtyReport($par) {
    FrailtyReport::execute($par);
}

class FrailtyReport extends SpecialPage {
    
    static $rows = array(
        "Nutritional Status" => array(
            "img" => "diet.png",
            "education" => array(
                "Diet and Nutrition" => "DietAndNutrition"
            ),
            "programs" => array(
                "Peer Coaching" => "PeerCoaching",
                "Cyber Seniors Webinar(s):" => array(
                    "Nutrition Apps" => "https://cyberseniors.org/previous-webinars/mobile-apps/nutrition-apps/",
                    "How to use Mealime" => "https://cyberseniors.org/previous-webinars/online-services/how-to-use-mealime/"
                )
            ),
            "community" => array(
                "Diet and Nutrition" => "CFN-DIET",
                "Activity → Exercise" => "CFN-ACT-EX"
            )
        ),
        "Oral Health" => array(
            "img" => "dental-care.png",
            "education" => array(
                "Diet and Nutrition" => "DietAndNutrition"
            ),
            "programs" => array(
            
            ),
            "community" => array(
                "Diet and Nutrition" => "CFN-DIET"
            )
        ),
        "Fatigue" => array(
            "img" => "tiredness.png",
            "education" => array(
                "Sleep" => "Sleep"
            ),
            "programs" => array(
                "Peer Coaching" => "PeerCoaching",
                "Community Connectors" => "CommunityConnectors",
                "Cyber Seniors Webinar(s):" => array(
                    "Apps for Better Sleep" => "https://cyberseniors.org/previous-webinars/mobile-apps/apps-for-better-sleep/"
                )
            ),
            "community" => array(
                "Activity → Exercise ↴ Movement and Mindfulness" => "CFN-ACT-EX-MOV",
                "Activity → Sleep" => "CFN-ACT-SLEEP",
                "Activity → Exercise" => "CFN-ACT-EX"
            )
        ),
        "Pain" => array(
            "img" => "back.png",
            "education" => array(
                "Activity" => "Activity"
            ),
            "programs" => array(
                "Peer Coaching" => "PeerCoaching"
            ),
            "community" => array(
                "Chronic Condition → Chronic<br />Pain" => "CFN-CHRONIC-PAIN",
                "Activity" => "CFN-ACT"
            )
        ),
        "Physical Activity" => array(
            "img" => "physical-activity.png",
            "education" => array(
                "Activity" => "Activity"
            ),
            "programs" => array(
                "Peer Coaching" => "PeerCoaching"
            ),
            "community" => array(
                "Activity" => "CFN-ACT"
            )
        ),
        "Strength" => array(
            "img" => "muscle.png",
            "education" => array(
                "Activity" => "Activity"
            ),
            "programs" => array(
                "Peer Coaching" => "PeerCoaching"
            ),
            "community" => array(
                "Activity → Exercise → Fitness" => "CFN-ACT-EX-FIT"
            )
        ),
        "Walking Speed" => array(
            "img" => "marathon.png",
            "education" => array(
                "Activity" => "Activity"
            ),
            "programs" => array(
            
            ),
            "community" => array(
                "Activity → Exercise" => "CFN-ACT-EX",
                "Activity → Exercise ↴ Movement and Mindfulness" => "CFN-ACT-EX-MOV",
                "Transportation → Driving<br />Programs" => "CFN-TRANSPORT-DRIVP"
            )
        ),
        "Falls and Balance" => array(
            "img" => "falling.png",
            "education" => array(
                "Activity" => "Activity"
            ),
            "programs" => array(
                "Peer Coaching" => "PeerCoaching",
                "Cyber Seniors Webinar(s):" => array(
                    "Apps for Better Sleep" => "https://cyberseniors.org/previous-webinars/mobile-apps/apps-for-better-sleep/",
                    "Assistive Tech for Fall Prevention" => "https://cyberseniors.org/previous-webinars/telemedicine/assistive-tech-for-fall-prevention/"
                )
            ),
            "community" => array(
                "Home & Care Partners ↴ Help at Home" => "CFN-HOMECARE-HELP",
                "Activity → Exercise ↴ Movement and Mindfulness" => "CFN-ACT-EX-MOV",
                "Transportation → Driving<br />Programs" => "CFN-TRANSPORT-DRIVP"
            )
        ),
        "Urinary Continence" => array(
            "img" => "urinary-tract.png",
            "education" => array(
                "Activity" => "Activity"
            ),
            "programs" => array(
                "Peer Coaching" => "PeerCoaching"
            ),
            "community" => array(
                "Healthcare Services → Clinics" => "CFN-HEALTH-CLINICS",
                "Activity → Exercise" => "CFN-ACT-EX"
            )
        ),
        "Memory" => array(
            "img" => "memory.png",
            "education" => array(
                "Activity" => "Activity",
                "Interact" => "Interact"
            ),
            "programs" => array(
                "Community Connectors" => "CommunityConnectors",
                "Cyber Seniors Webinar(s):" => array(
                    "Cognitive Function in Seniors" => "https://cyberseniors.org/previous-webinars/non-tech/cognitive-function-in-seniors/",
                    "Reminder Apps" => "https://cyberseniors.org/previous-webinars/mobile-apps/reminder-apps/"
                )
            ),
            "community" => array(
                "Interact" => "CFN-INT",
                "Activity" => "CFN-ACT"
            )
        ),
        "Mental Health" => array(
            "img" => "mental-health.png",
            "education" => array(
                "Interact" => "Interact",
                "Activity" => "Activity"
            ),
            "programs" => array(
                "Community Connectors" => "CommunityConnectors",
                "Peer Coaching" => "PeerCoaching",
                "Cyber Seniors Webinar(s):" => array(
                    "Staying Connected through technology" => "https://cyberseniors.org/stories/cyber-seniors-in-the-news/staying-connected-through-technology/",
                    "Guided Meditation on Self Love" => "https://cyberseniors.org/previous-webinars/featured-webinars/guided-meditation-on-kindness-and-self-love/",
                    "Craft for Mental Health" => "https://cyberseniors.org/previous-webinars/non-tech/craft-for-mental-health/",
                    "Online Mental Health Resources (with 7 cups demo)" => "https://cyberseniors.org/previous-webinars/online-services/online-mental-health-resources-with-7-cups-demo/"
                )
            ),
            "community" => array(
                "Interact" => "CFN-INT",
                "Activity" => "CFN-ACT",
                "Mental Health" => "CFN-MH"
            )
        ),
        "Multiple Medications" => array(
            "img" => "syringe.png",
            "education" => array(
                "Optimize Medication" => "OptimizeMedication"
            ),
            "programs" => array(
                "Peer Coaching" => "PeerCoaching",
                "Cyber Seniors Webinar(s):" => array(
                    "How to Fill Prescriptions Online" => "https://cyberseniors.org/previous-webinars/telemedicine/how-to-refill-prescriptions-online/",
                    "Medisafe Pill Reminder App" => "https://cyberseniors.org/previous-webinars/mobile-apps/medisafe-pill-reminder-app/"
                )
            ),
            "community" => array(
                "Vaccination/Optimize Medication" => "CFN-VAC",
                "Chronic Conditions" => "CFN-CHRONIC"
            )
        ),
        "Health Conditions" => array(
            "img" => "medical-chechup.png",
            "education" => array(
                "Diet and Nutrition" => "DietAndNutrition",
                "Activity" => "Activity"
            ),
            "programs" => array(
                "Peer Coaching" => "PeerCoaching",
                "Cyber Seniors Webinar(s):" => array(
                    "Healthy Hearts" => "https://cyberseniors.org/previous-webinars/exercise-health/heart-niagara-healthy-hearts/",
                    "Best Health Apps for Seniors" => "https://cyberseniors.org/previous-webinars/mobile-apps/best-health-apps-for-seniors-4/"
                )
            ),
            "community" => array(
                "Chronic Conditions" => "CFN-CHRONIC",
                "Diet and Nutrition" => "CFN-DIET",
                "Activity" => "CFN-ACT"
            )
        ),
        "Self-Perceived Health" => array(
            "img" => "fever.png",
            "education" => array(
                
            ),
            "programs" => array(
                "Peer Coaching" => "PeerCoaching"
            ),
            "community" => array(
            
            )
        ),
        "Sensory: Hearing and Vision" => array(
            "img" => "sensory.png",
            "education" => array(
                
            ),
            "programs" => array(
                "Cyber Seniors Webinar(s):" => array(
                    "Accessible Tech Projects & Services" => "https://cyberseniors.org/previous-webinars/device-basics/accessible-tech-products-services/",
                    "Set up Live Captions on your Device with Otter.ai" => "https://cyberseniors.org/previous-webinars/online-services/set-up-live-captions-on-your-device-with-otter-ai/",
                    "TV Ears: Audio Clarifying Device" => "https://cyberseniors.org/previous-webinars/buying-technology/tv-ears-audio-clarifying-device/"
                )
            ),
            "community" => array(
                "Disability Services" => "CFN-DIS",
                "Home & Care Partners ↴ Help at Home" => "CFN-HOMECARE-HELP",
                "Transportation → Driving<br />Programs" => "CFN-TRANSPORT-DRIVP"
            )
        )
    );
    
    function __construct() {
        SpecialPage::__construct("FrailtyReport", null, true, 'runFrailtyReport');
    }
    
    function userCanExecute($wgUser){
        $person = Person::newFromUser($wgUser);
        return $person->isLoggedIn();
    }
    
    function generateReport(){
        global $wgServer, $wgScriptPath, $config;
        $me = Person::newFromWgUser();
        $api = new UserFrailtyIndexAPI();
        $scores = $api->getFrailtyScore($me->getId());
        
        $margins = array('top'     => 1,
                         'right'   => 1,
                         'bottom'  => 1,
                         'left'    => 1);
        $html = "<html>
                    <head>
                        <link href='https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,400;0,700;0,900;1,400;1,700;1,900&family=Nunito+Sans:ital,wght@0,400;0,600;0,700;0,800;1,400;1,600;1,700;1,800&display=swap' rel='stylesheet'> 
                        <style>
                            @page {
                                margin-top: 0cm;
                                margin-right: 0cm;
                                margin-bottom: 0cm;
                                margin-left: 0cm;
                            }
                            
                            html {
                                width: 216mm;
                                position: relative;
                                overflow-x: hidden;
                            }
                            
                            body {
                                margin-top: {$margins['top']}cm;
                                margin-right: {$margins['right']}cm;
                                margin-bottom: {$margins['bottom']}cm;
                                margin-left: {$margins['left']}cm;
                                font-family: 'Nunito Sans';
                                font-weight: 600;
                                line-height: 1em;
                            }
                            
                            small {
                                font-size: 0.8em;
                                line-height: 1em;
                            }
                            
                            .logos {
                                white-space: nowrap;
                                width: 100%;
                                text-align: center;
                            }
                            
                            .logos img {
                                max-height: 65px;
                                margin-left: 3%;
                                margin-right: 3%;
                                vertical-align: middle;
                            }
                            
                            .title-box {
                                text-align: center;
                                float: right;
                                color: #06619b;
                                
                                margin-top: 1em;
                                margin-right: 4.5em;
                            }
                            
                            .title {
                                font-weight: 700;
                                font-size: 1.75em;
                                line-height: 1em;
                                text-decoration: underline;
                            }
                            
                            .frailtyStatus {
                                font-weight: 800;
                                margin-top: 1em;
                            }
                            
                            .list {
                                text-align: justify;
                                margin-left: 4.5em;
                                margin-right: 1em;
                                margin-top: 11em;
                            }
                            
                            .list p {
                                
                            }
                            
                            img.li {
                                height: 16px;
                                margin-right: 2px;
                            }
                            
                            table.recommendations {
                                width: 100%;
                                border-spacing: 0;
                                border-collapse: separate;
                                font-weight: 700;
                            }
                            
                            table.recommendations td  {
                                padding: 4px;
                                background: #cae8ff;
                                border-style: solid;
                                border-color: #8fb6d0;
                                vertical-align: top;
                                font-style: italic;
                            }
                            
                            table.recommendations th {
                                padding: 8px;
                                background: #89bde5;
                                vertical-align: top;
                                white-space: nowrap;
                                border-style: solid;
                                border-color: #5381a2;
                            }
                            
                            table.recommendations th.dark {
                                background: #67a0cd;
                                padding: 0 !important;
                                vertical-align: middle;
                            }
                            
                            td, th {
                                border-width: 0 3px 3px 0;
                                line-height: 1em;
                            }
                            
                            th.hack {
                                border-width: 0 3px 0 0;
                            }
                            
                            td:last-child, th:last-child {
                                border-width: 0 0 3px 0;
                            }
                            
                            table img {
                                height: 50px;
                                display: inline-block;
                            }
                            
                            table p {
                                margin-top: 0;
                            }
                            
                            a, a:visited {
                                color: #005f9d;
                                text-decoration: none;
                            }
                            
                            a:hover, a:focus {
                                color: #e97936;
                            }
                            
                            .cb {
                                display: inline-block;
                                vertical-align: top;
                                padding: 0;
                                margin: 0;
                                line-height: 1em;
                            }
                            
                        </style>
                    </head>
                    <body>
                        <img src='{$wgServer}{$wgScriptPath}/skins/bg_top.png' style='z-index: -2; position: absolute; top:0; left: 0; right:0; width: 216mm;' />
                        <div class='logos'>
                            <img src='{$wgServer}{$wgScriptPath}/skins/logo3.png' />
                            <img style='max-height: 100px;' src='{$wgServer}{$wgScriptPath}/skins/logo2.png' />
                            <img src='{$wgServer}{$wgScriptPath}/skins/logo1.png' />
                        </div>
                        <div class='title-box'>
                            <div class='title'>
                                My Frailty Status: Report from<br />
                                Healthy Aging Assessment
                            </div>
                            <div class='frailtyStatus'>My Frailty Status: <u>{$scores['Label']}</u></div>
                        </div>
                        <div class='list'>
                            <p><img class='li' src='{$wgServer}{$wgScriptPath}/skins/li.png' />This report shows the items that went into your frailty status.  Where a need was identified from your answers, some recommended resources appear in that topic to address that specific item.  If you do not see any recommendations, it means that no needs were identified from your answers.</p>
                            <p><img class='li' src='{$wgServer}{$wgScriptPath}/skins/li.png' />While the behaviours recommended for all AVOID components play a role in a healthy lifestyle, you may want to focus on one of a few at a time - this report can help you decide where you start and focus your efforts.  You can use this as a place to start on your healthy aging journey to help you develop an action plan around one or more of the topics that can best help slow the onset of frailty for you personally.</p>
                            <p><img class='li' src='{$wgServer}{$wgScriptPath}/skins/star.png' />The recommendations throughout this program are meant to support healthy behaviour.  They are not clinical recommendations, for which you should seek advice from your health care providers (example: doctor, pharmacist, dentist)</p>
                        </div>
                        <br />
                        <br />
                        <table class='recommendations' cellspacing='0' style='width: 100%;'>
                            <tr>
                                <th rowspan='2' style='min-width: 6em; width: 6em; padding-bottom: 0; position: relative;'>
                                    <div style='line-height: 1em; position: absolute; top: 8px; left: 0;width:100%; text-align: center;'>Need<br />Identified?<br />(Y/N)</div>
                                </th>
                                <th rowspan='2' style='min-width: 6em; width: 6em;'>
                                    Topic
                                </th>
                                <th class='dark' colspan='3'>
                                    AVOID Frailty Program Support Recommendation
                                </th>
                            </tr>
                            <tr>
                                <th align='left'>
                                    <small><i>AVOID Frailty<br />Education<br />Topic<br /></i></small>
                                </th>
                                <th align='left'>
                                    <small><i>AVOID Frailty<br />Programs<br /></i></small>
                                </th>
                                <th align='left' style='width: 13em;'>
                                    <small><i>Community Program<br />Category (Find these in the <br />Community Program Library)<br /></i></small>
                                </th>
                            </tr>";
        foreach(self::$rows as $key => $row){
            $need = "N";
            $education = "";
            $programs = "";
            $community = "";
            if($scores[$key] > 0){
                $need = "Y";
                foreach($row['education'] as $k => $e){
                    $education .= "<p><a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=EducationModules/{$e}' target='_blank'>{$k}</a></p>";
                }
                foreach($row['programs'] as $k => $p){
                    if(is_array($p)){
                        $links = array();
                        foreach($p as $k1 => $p1){
                            $links[] = "<a href='{$p1}' target='_blank'>{$k1}</a>";
                        }
                        $programs .= "<p>{$k} ".implode(", ", $links)."</p>";
                    }
                    else{
                        $programs .= "<p><a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=Programs/{$p}' target='_blank'>{$k}</a></p>";
                    }
                }
                foreach($row['community'] as $k => $p){
                    $k = preg_replace("/(.*(^|→|↴))(?!.*(→|↴))(.*)/", "$1<a style='vertical-align:top;' target='_blank' href='{$wgServer}{$wgScriptPath}/index.php/Special:PharmacyMap#/{$p}'>$4</a>", $k);
                    $k = str_replace("→", "</span>→<span class='cb'>", $k);
                    $k = str_replace("↴", "</span>↴<span class='cb' style='width: 100%; text-align: right;'>", $k);
                    $community .= "<p><span class='cb'>{$k}</span></p>";
                }
            }
            $html .= "      <tr>
                                <td align='center' style='padding-top: 1em; font-style: initial;'>{$need}</td>
                                <td align='center'><img src='{$wgServer}{$wgScriptPath}/extensions/Reporting/Report/SpecialPages/AVOID/images/{$row['img']}' alt='{$key}' /><br />{$key}</td>
                                <td align='center'>{$education}</td>
                                <td align='center' style='font-size: 0.8em;'>{$programs}</td>
                                <td style='font-size: 0.9em;'>{$community}</td>
                            </tr>";
        }
        $html .= "      </table><br /><br /><br /><br /><br /><br />
                        <img src='{$wgServer}{$wgScriptPath}/skins/bg_bottom.png' style='z-index: -2; position: absolute; bottom:0; left: 0; right:0; width: 216mm;' />
                    </body>
                </html>";
        
        $margin = (isset($_GET['preview'])) ? 0 : 5;
        $html = str_replace("↴", "<span class='cb' style='margin-top:{$margin}px; margin-bottom:-{$margin}px; vertical-align: top; font-family: dejavu sans; font-style: initial;'>&nbsp;↴&nbsp;</span><br />", $html);
        $html = str_replace("→", "<span class='cb' style='margin-top:".($margin/2)."px; margin-bottom:-".($margin/2)."px; vertical-align: top; font-family: dejavu sans; font-style: initial;'>&nbsp;→&nbsp;</span>", $html);
        
        return $html;
    }
    
    function execute($par){
        global $wgServer, $wgScriptPath, $dompdfOptions;
        $dir = dirname(__FILE__);
        require_once($dir . '/../../../../../config/dompdf_config.inc.php');
        $html = $this->generateReport();
        if(isset($_GET['preview'])){
            echo $html;
            exit;
        }
        $dompdfOptions->setFontHeightRatio(1.0);
        $dompdfOptions->setDpi(96);
        $dompdf = new Dompdf\Dompdf($dompdfOptions);
        $dompdf->setPaper('letter', 'portrait');
        
        $dompdf->load_html($html);
        $dompdf->render();
        header("Content-Type: application/pdf");
        echo $dompdf->output();
        exit;
        
    }
    
}

?>
