<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['FrailtyReport'] = 'FrailtyReport'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['FrailtyReport'] = $dir . 'FrailtyReport.i18n.php';
$wgSpecialPageGroups['FrailtyReport'] = 'reporting-tools';

function runFrailtyReport($par) {
    FrailtyReport::execute($par);
}

class FrailtyReport extends SpecialPage {
    
    static $healthRows = array(
        "Nutritional Status" => array(
            "img" => "diet.png",
            "no" => "Great job, keep it up!<br />
                     Protein, calcium and vitamin D are particularly important to maintain strong bones and muscles.",
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
            "no" => "Way to go! <br />
                     Good oral hygiene practices can help you to avoid tooth pain that makes it difficult to eat. Make sure you continue with regular visits to your dentist.",
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
            "no" => "Getting older means your sleep will change, but not necessarily impact your daily living. It looks like you’ve got this under control!",
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
            "no" => "If you’re not experiencing pain or discomfort, that’s great! Age isn’t synonymous with aches and pains, but by the looks of things, you already know that!",
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
            "no" => "You are extending independent living, reducing your risk of many chronic conditions, and reducing your risk of falls. Great job!",
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
            "no" => "Keep up your strength! We naturally lose muscle tone as we age, so it’s great you’re combating that now!",
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
            "no" => "Walking is a great exercise you can continue to do as you age. It’s great for strength, mobility and reducing your risk of falling. Sounds like you aren’t slowing down, keep it up!",
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
            "no" => "Did you know that every 12 seconds a Canadian aged 65years+ experiences a fall? Sounds like you’ve reduced your risk. Way to go!",
            "education" => array(
                "Activity" => "Activity"
            ),
            "programs" => array(
                "Peer Coaching" => "PeerCoaching"
            ),
            "community" => array(
                "Home & Care Partners ↴ Help at Home" => "CFN-HOMECARE-HELP",
                "Activity → Exercise ↴ Movement and Mindfulness" => "CFN-ACT-EX-MOV",
                "Transportation → Driving<br />Programs" => "CFN-TRANSPORT-DRIVP"
            )
        ),
        "Urinary Continence" => array(
            "img" => "urinary-tract.png",
            "no" => "You are doing well. Maintaining a healthy lifestyle is an important strategy to avoid incontinence. If you do develop problems, see your healthcare provider since incontinence is not an inevitable part of aging and there are measures that can be taken to help.",
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
            "no" => "You are doing well. Just as exercise is important to keep muscle strength, doing activities which require mental effort are important for brain function to be maintained.",
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
            "no" => "Mental Health problems are prevalent at every age. If your mental health status changes, speak with your family doctor.",
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
            "no" => "Continue to conduct annual medication reviews with your doctor or pharmacist. These reviews could result in reducing a dose, changing medications or stopping an unnecessary medication.",
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
            "no" => "",
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
            "no" => "",
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
            "no" => "If anything changes with vision or hearing, visit your appropriate health care provider.",
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
    
    static $behavioralRows = array(
        "Activity" => array(
            "img" => "Activity.png",
            "no" => "Way to stay active! If you want to learn more, you can view the Activity module in education resources or any activity-related webinar within Cyber-Seniors.",
            "education" => array(
                "Activity" => "Activity"
            ),
            "programs" => array(
                "Peer Coaching" => "PeerCoaching",
                "Cyber Seniors Webinar(s):" => array(
                    "Yoga for Seniors: Benefits and How to access online" => "https://cyberseniors.org/previous-webinars/mobile-apps/yoga-for-seniors-how-to-access-online/",
                    "Exercise class with Renee" => "https://cyberseniors.org/previous-webinars/featured-webinars/exercise-class-with-renee-wed/",
                    "Zumba Gold with Renee" => "https://cyberseniors.org/previous-webinars/exercise-health/zumba-gold-with-renee/"
                )
            ),
            "community" => array(
                "Activity" => "CFN-ACT",
                "Interact" => "CFN-INT"
            )
        ),
        "Vaccination" => array(
            "img" => "Vaccination.png",
            "no" => "As we age, it’s harder to recover from infectious diseases like the flu, pneumonia, and COVID-19. Way to keep up with your vaccines!",
            "education" => array(
                "Vaccination" => "Vaccination"
            ),
            "programs" => array(
                "Peer Coaching" => "PeerCoaching"
            ),
            "community" => array(
                "Vaccination/Optimize Medication" => "CFN-VAC",
                "Transportation" => "CFN-TRANSPORT"
            )
        ),
        "Optimize Medication" => array(
            "img" => "OptimizeMedication.png",
            "no" => "It’s great that you feel like you have your medications under control. 
If you need help with managing the medications you are on, visit the following Cyber-Seniors Webinars: How to Fill Prescriptions Online, Medisafe Pill Reminder App",
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
                "Activity → Exercise" => "CFN-ACT-EX",
                "Interact" => "CFN-INT"
            )
        ),
        "Interact" => array(
            "img" => "Interact.png",
            "no" => "Keep up with your social interactions. It provides you with mental and physical health benefits!<br />
                     If you want to learn more, you can view the Interact module in education resources",
            "education" => array(
                "Interact" => "Interact"
            ),
            "programs" => array(
                "Peer Coaching" => "PeerCoaching",
                "Community Connectors" => "CommunityConnectors",
                "Cyber Seniors Webinar(s):" => array(
                    "Staying Connected through technology" => "https://cyberseniors.org/stories/cyber-seniors-in-the-news/staying-connected-through-technology/"
                )
            ),
            "community" => array(
                "Interact" => "CFN-INT",
                "Activity" => "CFN-ACT"
            )
        ),
        "Diet and Nutrition" => array(
            "img" => "DietAndNutrition.png",
            "no" => "Great job, keep it up! <br />
                     Continue to incorporate protein, calcium and vitamin D  - to maintain strong bones and muscles.<br />
                     <br />
                    If you want to learn more about diet and nutrition, you can view the Nutrition & Diet education module, or any nutrition-related webinar within Cyber-Seniors.",
            "education" => array(
                "Diet and Nutrition" => "DietAndNutrition"
            ),
            "programs" => array(
                "Peer Coaching" => "PeerCoaching",
                "Cyber Seniors Webinar(s):" => array(
                    "Ordering Groceries Online" => "https://cyberseniors.org/previous-webinars/mobile-apps/ordering-groceries-online/",
                    "All Recipes" => "https://cyberseniors.org/previous-webinars/mobile-apps/all-recipes/",
                    "Nutrition Apps" => "https://cyberseniors.org/previous-webinars/mobile-apps/nutrition-apps/",
                    "How to Use Mealime" => "https://cyberseniors.org/previous-webinars/online-services/how-to-use-mealime/"
                )
            ),
            "community" => array(
                "Diet and Nutrition" => "CFN-DIET",
                "Activity" => "CFN-ACT"
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
    
    function drawRow($key, $row, $scores){
        global $wgServer, $wgScriptPath;
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
        $html = "<tr>
                    <td align='center' style='padding-top: 1em; font-style: initial;'>{$need}</td>
                    <td align='center'><img src='{$wgServer}{$wgScriptPath}/extensions/Reporting/Report/SpecialPages/AVOID/images/{$row['img']}' alt='{$key}' /><br />{$key}</td>";
        if($need == "Y"){
            $html .= "<td align='center'>{$education}</td>
                    <td align='center' style='font-size: 0.8em;'>{$programs}</td>
                    <td style='font-size: 0.9em;'>{$community}</td>";
        }
        else{
            $html .= "<td colspan='3'>{$row['no']}</td>";
        }
        $html .= "</tr>";
        return $html;
    }
    
    function generateReport(){
        global $wgServer, $wgScriptPath, $config;
        $dir = dirname(__FILE__) . '/';
        require_once($dir . '/../../../../../Classes/SmartDomDocument/SmartDomDocument.php');
        $me = Person::newFromWgUser();
        $api = new UserFrailtyIndexAPI();
        $scores = $api->getFrailtyScore($me->getId());

        $pdfNoDisplay = (!isset($_GET['preview'])) ? ".pdfnodisplay { display:none }" : "";

        $margins = array('top'     => 1,
                         'right'   => 1,
                         'bottom'  => 1,
                         'left'    => 1);
        $html = "<html>
                    <head>
                        <script language='javascript' type='text/javascript' src='{$wgServer}{$wgScriptPath}/scripts/jquery.min.js?version=3.4.1'></script>
                        <link href='https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,400;0,700;0,900;1,400;1,700;1,900&family=Nunito+Sans:ital,wght@0,400;0,600;0,700;0,800;1,400;1,600;1,700;1,800&display=swap' rel='stylesheet'>
                        <meta name='viewport' content='width=device-width, initial-scale=1.0' />
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
                                transform-origin: top left;
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
                            
                            $pdfNoDisplay
                            
                            .title-box {
                                text-align: center;
                                float: right;
                                color: #06619b;
                                
                                margin-top: 1em;
                                margin-right: 1.5em;
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
                            
                            table.recommendations th.dark-top {
                                background: #67a0cd;
                                padding: 4px;
                                vertical-align: middle;
                                font-size: 0.9em;
                            }
                            
                            table.recommendations td.white {
                                border: none;
                                background: white;
                                height: 1em;
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
                                My Frailty and Behavioural Status:<br />
                                Report from Healthy Aging Assessment
                            </div>
                            <div class='frailtyStatus'>My Frailty Status: <u>{$scores['Label']}</u></div>
                            <div class='pdfnodisplay' style='margin-top:1em;'>Your recommendations with direct links to resources are below.<br />You can also print your personal report <a href='{$wgServer}{$wgScriptPath}/index.php/Special:FrailtyReport' target='_blank'><b><u>here</u></b></a>.</div>
                        </div>
                        <div class='list'>
                            <p><img class='li' src='{$wgServer}{$wgScriptPath}/skins/li.png' />Your frailty status is a calculation based on your stated health outcomes that may be improved by meeting the behavioural recommendations for each AVOID component. This report reflects your answers in the assessment for those two sections. Where a risk was identified from your answers, some recommended resources appear in that topic to address that specific item. If you do not see any recommendations, it means that no risks were identified from your answers.</p>
                            <p><img class='li' src='{$wgServer}{$wgScriptPath}/skins/li.png' />While the behaviours recommended for all AVOID components play a role in a healthy lifestyle, you may want to focus on one or a few at a time - this report can help you decide where to start and focus your efforts. You can use this as a place to start and to refer back to throughout your healthy aging journey to help you develop an action plan around one or more of the topics that can best help slow the onset of frailty for you personally.</p>
                            <p><img class='li' src='{$wgServer}{$wgScriptPath}/skins/star.png' />The recommendations throughout this program are meant to support healthy behaviour.  They are not clinical recommendations, for which you should seek advice from your health care providers (example: doctor, pharmacist, dentist)</p>
                        </div>
                        <br />
                        <br />
                        <table class='recommendations' cellspacing='0' style='width: 100%;'>
                            <tr>
                                <th class='dark-top' colspan='5'>The following risks and recommendations are from the health outcomes section of the assessment</th>
                            </tr>
                            <tr>
                                <th rowspan='2' style='min-width: 6em; width: 6em; padding-bottom: 0; position: relative;'>
                                    <div style='line-height: 1em; position: absolute; top: 8px; left: 0;width:100%; text-align: center;'>Risk<br />Identified?<br />(Y/N)</div>
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
        foreach(self::$healthRows as $key => $row){
            $html .= $this->drawRow($key, $row, $scores);
        }
        
        $html .= "<tr><td class='white' colspan='5'></td></tr>
                  <tr style='page-break-after: avoid;'><th class='dark-top' colspan='5'>The following risks and recommendations are from the behavioural portion of the assessment</th></tr>";
        foreach(self::$behavioralRows as $key => $row){
            $html .= $this->drawRow($key, $row, $scores["Behavioral"]);
        }
        $html .= "      </table><br /><br /><br /><br /><br /><br />
                        <img src='{$wgServer}{$wgScriptPath}/skins/bg_bottom.png' style='z-index: -2; position: absolute; bottom:0; left: 0; right:0; width: 216mm;' />
                        <script type='text/javascript'>
                            var initialWidth = $(window).width();
                            
                            $(window).resize(function(){
                                $('html').width('100%');
                                var desiredWidth = $(window).width();
                                $('html').width('216mm');
                                var scaleFactor = desiredWidth/initialWidth;
                                $('html').css('transform', 'scale(' + scaleFactor + ')');
                            }).resize();
                        </script>
                    </body>
                </html>";
        
        $margin = (isset($_GET['preview'])) ? 0 : 5;
        $html = str_replace("↴", "<span class='cb' style='margin-top:{$margin}px; margin-bottom:-{$margin}px; vertical-align: top; font-family: dejavu sans; font-style: initial;'>&nbsp;↴&nbsp;</span><br />", $html);
        $html = str_replace("→", "<span class='cb' style='margin-top:".($margin/2)."px; margin-bottom:-".($margin/2)."px; vertical-align: top; font-family: dejavu sans; font-style: initial;'>&nbsp;→&nbsp;</span>", $html);
        
        if(!isset($_GET['preview'])){
            $dom = new SmartDomDocument();
            $dom->loadHTML($html);
            $as = $dom->getElementsByTagName("a");
            for($i=0; $i<$as->length; $i++){
                $a = $as->item($i);
                if($a->getAttribute('class') != 'anchor' && 
                   $a->getAttribute('class') != 'mce-item-anchor' &&
                   $a->getAttribute('class') != 'externalLink' && 
                   $a->textContent != ""){
                    $i--;
                    DOMRemove($a);
                }
            }
            $html = "$dom";
        }
        
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
