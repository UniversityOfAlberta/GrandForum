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
        "A" => array(
            "Activity" => array(
                "img" => "Activity.png",
                "text" => "<en>Activity</en><fr>Activité physique</fr>",
                "no" => "<en>Keep up the good work!</en><fr>Continuez votre bon travail!</fr>",
                "education" => array(
                    "<en>Activity</en><fr>Activité physique</fr>" => "Activity"
                ),
                "programs" => array(
                    "<en>Peer Coaching</en><fr>Coaching par les pairs</fr>" => "PeerCoaching"
                ),
                "community" => array(
                    "<en>Activity</en><fr>Activité physique</fr>" => "CFN-ACT"
                )
            ),
            "Falls and Balance" => array(
                "img" => "falling.png",
                "text" => "<en>Falls and Balance</en><fr>Chutes et équilibre</fr>",
                "no" => "<en>Keep up the good work!</en><fr>Continuez votre bon travail!</fr>",
                "education" => array(
                    "<en>Falls Prevention</en><fr>Prévention des chutes</fr>" => "FallsPrevention",
                    "<en>Resources</en><fr>Ressources</fr>" => "index.php/Special:EducationResources?topic=FallsPrevention"
                ),
                "programs" => array(
                    "<en>Otago Exercise Program</en><fr>Coaching par les pairs</fr>" => "Otago"
                ),
                "community" => array(
                    "<en>Activity</en><fr>Activité physique</fr> → <en>Gentle</en><fr>Doux</fr>" => "CFN-ACT-GEN",
                    "<en>Home & Care Partners</en><fr>Services de soutien<br />et de soins à domicile</fr> ↴ <en>Help at Home</en><fr>Aide à domicile</fr>" => "CFN-HOMECARE-HELP"
                )
            ),
            "Fatigue" => array(
                "img" => "tiredness.png",
                "text" => "<en>Fatigue</en><fr>Fatigue</fr>",
                "no" => "<en>Keep up the good work!</en><fr>Continuez votre bon travail!</fr>",
                "education" => array(
                    "<en>Sleep</en><fr>Sommeil</fr>" => "Sleep"
                ),
                "programs" => array(
                    "<en>Peer Coaching</en><fr>Coaching par les pairs</fr>" => "PeerCoaching"
                ),
                "community" => array(
                    "<en>Activity</en><fr>Activité physique</fr> → <en>Exercise</en><fr>Exercice</fr> ↴ <en>Movement and Mindfulness</en><fr>Mouvement et pleine conscience</fr>" => "CFN-ACT-EX-MOV",
                    "<en>Activity</en><fr>Activité physique</fr> → <en>Sleep</en><fr>Sommeil</fr>" => "CFN-ACT-SLEEP",
                    "<en>Activity</en><fr>Activité physique</fr> → <en>Exercise</en><fr>Exercice</fr>" => "CFN-ACT-EX"
                )
            ),
            "Strength" => array(
                "img" => "muscle.png",
                "text" => "<en>Strength</en><fr>Force physique</fr>",
                "no" => "<en>Keep up the good work!</en><fr>Continuez votre bon travail!</fr>",
                "education" => array(
                    "<en>Resources</en><fr>Ressources</fr>" => "index.php/Special:EducationResources?topic=Activity"
                ),
                "programs" => array(

                ),
                "community" => array(
                    "<en>Activity</en><fr>Activité physique</fr> → <en>Exercise</en><fr>Exercice</fr> → <en>Fitness</en><fr>Conditionnement physique</fr>" => "CFN-ACT-EX-FIT"
                )
            ),
            "Walking Speed" => array(
                "img" => "marathon.png",
                "text" => "<en>Walking Speed</en><fr>Vitesse de marche</fr>",
                "no" => "<en>Keep up the good work!</en><fr>Continuez votre bon travail!</fr>",
                "education" => array(
                    "<en>Activity</en><fr>Activité physique</fr>" => "Activity"
                ),
                "programs" => array(
                    "<en>Otago Exercise Program</en><fr>Coaching par les pairs</fr>" => "Otago"
                ),
                "community" => array(
                    "<en>Activity</en><fr>Activité physique</fr> → <en>Gentle</en><fr>Doux</fr>" => "CFN-ACT-GEN"
                )
            )
        ),
        "V" => array(
            "Vaccinate" => array(
                "img" => "Vaccination.png",
                "text" => "<en>Vaccination</en><fr>Vaccination</fr>",
                "no" => "<en>Keep up the good work!</en><fr>Continuez votre bon travail!</fr>",
                "education" => array(
                    "<en>Vaccination</en><fr>Vaccination</fr>" => "Vaccination",
                    "<en>Resources</en><fr>Ressources</fr>" => "index.php/Special:EducationResources?topic=Vaccination"
                ),
                "programs" => array(

                ),
                "community" => array(
                    "<en>Vaccination/Optimize Medication</en><fr>Vaccination et optimisation des médicaments</fr>" => "CFN-VAC"
                )
            )
        ),
        "O" => array(
            "Optimize Medication" => array(
                "img" => "OptimizeMedication.png",
                "text" => "<en>Optimize Medication</en><fr>Optimisation des médicaments</fr>",
                "no" => "<en>Keep up the good work!</en><fr>Continuez votre bon travail!</fr>",
                "education" => array(
                     "<en>Optimize Medication</en><fr>Optimisation des médicaments</fr>" => "OptimizeMedication",
                     "<en>Resources</en><fr>Ressources</fr>" => "index.php/Special:EducationResources?topic=OptimizeMedication"
                ),
                "programs" => array(
                    "Cyber Seniors Webinar(s):" => array(
                        "How to Fill Prescriptions Online" => "https://cyberseniors.org/previous-webinars/exercise-health/how-to-fill-prescriptions-online/",
                        "Medisafe Pill Reminder App" => "https://cyberseniors.org/previous-webinars/apps-and-online-services/medisafe-pill-reminder-app/"
                    )
                ),
                "community" => array(
                    "<en>Vaccination/Optimize Medication</en><fr>Vaccination et optimisation des médicaments</fr>" => "CFN-VAC"
                )
            ),
            "Multiple Medications" => array(
                "img" => "syringe.png",
                "text" => "<en>Multiple Medications</en><fr>Médicaments multiples</fr>",
                "no" => "<en>Keep up the good work!</en><fr>Continuez votre bon travail!</fr>",
                "education" => array(
                    "Optimize Medication" => "OptimizeMedication"
                ),
                "programs" => array(
                    "<en>Peer Coaching</en><fr>Coaching par les pairs</fr>" => "PeerCoaching"
                ),
                "community" => array(
                    "<en>Vaccination/Optimize Medication</en><fr>Vaccination et optimisation des médicaments</fr>" => "CFN-VAC",
                    "<en>Chronic Conditions</en><fr>Maladies chroniques</fr>" => "CFN-CHRONIC"
                )
            )
        ),
        "I" => array(
            "Interact" => array(
                "img" => "Interact.png",
                "text" => "<en>Interact</en><fr>Vie sociale</fr>",
                "no" => "<en>Keep up the good work!</en><fr>Continuez votre bon travail!</fr>",
                "education" => array(
                    "<en>Interact</en><fr>Vie sociale</fr>" => "Interact"
                ),
                "programs" => array(
                    "<en>Peer Coaching</en><fr>Coaching par les pairs</fr>" => "PeerCoaching"
                ),
                "community" => array(
                    "<en>Interact</en><fr>Vie sociale</fr>" => "CFN-INT",
                    "<en>Activity</en><fr>Activité physique</fr>" => "CFN-ACT"
                )
            ),
            "Mental Health" => array(
                "img" => "mental-health.png",
                "text" => "<en>Mental Health</en><fr>Santé mentale</fr>",
                "no" => "<en>Keep up the good work!</en><fr>Continuez votre bon travail!</fr>",
                "education" => array(
                    "<en>Interact</en><fr>Vie sociale</fr>" => "Interact",
                    "<en>Resources</en><fr>Ressources</fr>" => "index.php/Special:EducationResources?topic=Interact&resources=MentalHealth"
                ),
                "programs" => array(
                    "<en>Peer Coaching</en><fr>Coaching par les pairs</fr>" => "PeerCoaching"
                ),
                "community" => array(
                    "<en>Interact</en><fr>Vie sociale</fr>" => "CFN-INT",
                    "<en>Activity</en><fr>Activité physique</fr>" => "CFN-ACT",
                    "<en>Mental Health</en><fr>Santé mentale</fr>" => "CFN-MH"
                )
            ),
            "Sensory: Hearing and Vision" => array(
                "img" => "sensory.png",
                "text" => "<en>Communication</en><fr>Communication</fr>",
                "no" => "<en>Keep up the good work!</en><fr>Continuez votre bon travail!</fr>",
                "education" => array(
                    
                ),
                "programs" => array(

                ),
                "community" => array(
                    "<en>Disability Services</en><fr>Services liés à l’incapacité</fr>" => "CFN-DIS",
                    "<en>Home & Care Partners</en><fr>Services de soutien<br />et de soins à domicile</fr> ↴ <en>Help at Home</en><fr>Aide à domicile</fr>" => "CFN-HOMECARE-HELP",
                    "<en>Interact</en><fr>Vie sociale</fr> → <en>Communication</en><fr>Communication</fr>" => "CFN-INT-COM"
                )
            )
        ),
        "D" => array(
            "Diet & Nutrition" => array(
                "img" => "DietAndNutrition.png",
                "text" => "<en>Diet and Nutrition</en><fr>Alimentation</fr>",
                "no" => "<en>Keep up the good work!</en><fr>Continuez votre bon travail!</fr>",
                "education" => array(
                    "<en>Diet and Nutrition</en><fr>Alimentation</fr>" => "DietAndNutrition"
                ),
                "programs" => array(
                    "<en>Peer Coaching</en><fr>Coaching par les pairs</fr>" => "PeerCoaching"
                ),
                "community" => array(
                    "<en>Diet and Nutrition</en><fr>Alimentation</fr>" => "CFN-DIET",
                    "<en>Activity</en><fr>Activité physique</fr>" => "CFN-ACT"
                )
            ),
            "Nutritional Status" => array(
                "img" => "diet.png",
                "text" => "<en>Appetite</en><fr>État nutritionnel</fr>",
                "no" => "<en>Keep up the good work!</en><fr>Continuez votre bon travail!</fr>",
                "education" => array(
                    "<en>Resources</en><fr>Ressources</fr>" => "index.php/Special:EducationResources?topic=DietAndNutrition&resources=Appetite"
                ),
                "programs" => array(

                ),
                "community" => array(
                    "<en>Diet and Nutrition</en><fr>Alimentation</fr>" => "CFN-DIET"
                )
            ),
            "Oral Health" => array(
                "img" => "dental-care.png",
                "text" => "<en>Oral Health</en><fr>Santé bucco-dentaire</fr>",
                "no" => "<en>Keep up the good work!</en><fr>Continuez votre bon travail!</fr>",
                "education" => array(
                    "<en>Diet and Nutrition</en><fr>Alimentation</fr>" => "DietAndNutrition"
                ),
                "programs" => array(
                
                ),
                "community" => array(
                    "<en>Diet and Nutrition</en><fr>Alimentation</fr> → <en>Dental</en><fr>Soins dentaires</fr>" => "CFN-DIET-DENTAL"
                )
            )
        )
    );
    
    static $otherRows = array(
        "Pain" => array(
            "img" => "back.png",
            "text" => "<en>Pain</en><fr>Douleur</fr>",
            "no" => "<en>Keep up the good work!</en><fr>Continuez votre bon travail!</fr>",
            "education" => array(
                "<en>Activity</en><fr>Activité physique</fr>" => "Activity"
            ),
            "programs" => array(
                "<en>Peer Coaching</en><fr>Coaching par les pairs</fr>" => "PeerCoaching"
            ),
            "community" => array(
                "<en>Chronic Condition</en><fr>Maladies chroniques</fr> → <en>Chronic<br />Pain</en><fr>Douleur chronique</fr>" => "CFN-CHRONIC-PAIN"
            )
        ),
        "Urinary Continence" => array(
            "img" => "urinary-tract.png",
            "text" => "<en>Urinary Continence</en><fr>Continence urinaire</fr>",
            "no" => "<en>Keep up the good work!</en><fr>Continuez votre bon travail!</fr>",
            "education" => array(
                "<en>Activity</en><fr>Activité physique</fr>" => "Activity"
            ),
            "programs" => array(
                "<en>Peer Coaching</en><fr>Coaching par les pairs</fr>" => "PeerCoaching"
            ),
            "community" => array(
                
            )
        ),
        "Memory" => array(
            "img" => "memory.png",
            "text" => "<en>Memory</en><fr>Mémoire</fr>",
            "no" => "<en>Keep up the good work!</en><fr>Continuez votre bon travail!</fr>",
            "education" => array(
                "<en>Activity</en><fr>Activité physique</fr>" => "Activity",
                "<en>Interact</en><fr>Vie sociale</fr>" => "Interact"
            ),
            "programs" => array(

            ),
            "community" => array(
                "<en>Chronic Conditions</en><fr>Maladies chroniques</fr>" => "CFN-CHRONIC-DEMENTIA"
            )
        ),
        "Health Conditions" => array(
            "img" => "medical-chechup.png",
            "text" => "<en>Health Conditions</en><fr>Affections de santé</fr>",
            "no" => "<en>Keep up the good work!</en><fr>Continuez votre bon travail!</fr>",
            "education" => array(
                "<en>Diet and Nutrition</en><fr>Alimentation</fr>" => "DietAndNutrition",
                "<en>Activity</en><fr>Activité physique</fr>" => "Activity",
                "Cyber Seniors Webinar(s):" => array(
                    "Apps for Better Sleep" => "https://cyberseniors.org/previous-webinars/mobile-apps/apps-for-better-sleep/"
                )
            ),
            "programs" => array(
                "<en>Peer Coaching</en><fr>Coaching par les pairs</fr>" => "PeerCoaching"
            ),
            "community" => array(
                "<en>Chronic Conditions</en><fr>Maladies chroniques</fr>" => "CFN-CHRONIC"
            )
        ),
        "Self-Perceived Health" => array(
            "img" => "fever.png",
            "text" => "<en>Self-Perceived Health</en><fr>État de santé autoévalué</fr>",
            "no" => "<en>Keep up the good work!</en><fr>Continuez votre bon travail!</fr>",
            "education" => array(
                
            ),
            "programs" => array(
                "<en>Peer Coaching</en><fr>Coaching par les pairs</fr>" => "PeerCoaching"
            ),
            "community" => array(
            
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
    
    function drawRow($comp, $topics, $scores){
        global $wgServer, $wgScriptPath, $wgLang;
        $subTopics = array();
        $education = array();
        $programs = array();
        $community = array();
        $nos = array();
        foreach($topics as $key => $topic){
            if(isset($scores[$key])){
                $subTopics[$key] = "<p><img src='{$wgServer}{$wgScriptPath}/extensions/Reporting/Report/SpecialPages/AVOID/images/{$topic['img']}' alt='{$key}' /><br />{$topic['text']}</p>";
            }
            if(@$scores[$key] > 0 || @$scores["Behavioral"][$key] > 0){
                foreach($topic['education'] as $k => $e){
                    if(is_array($e)){
                        $links = array();
                        foreach($e as $k1 => $e1){
                            $links[] = "<a href='{$e1}' target='_blank'>{$k1}</a>";
                        }
                        $education[$key][] = "<p>{$k} ".implode(", ", $links)."</p>";
                    }
                    else{
                        if(strstr($e, "http") !== false){
                            $education[$key][] = "<p><a href='{$e}' target='_blank'>{$k}</a></p>";
                        }
                        else if(strstr($e, "index.php") !== false){
                            $education[$key][] = "<p><a href='{$wgServer}{$wgScriptPath}/{$e}' target='_blank'>{$k}</a></p>";
                        }
                        else{
                            $education[$key][] = "<p><a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=EducationModules/{$e}' target='_blank'>{$k}</a></p>";
                        }
                    }
                }
                foreach($topic['programs'] as $k => $p){
                    if(is_array($p)){
                        $links = array();
                        foreach($p as $k1 => $p1){
                            $links[] = "<a href='{$p1}' target='_blank'>{$k1}</a>";
                        }
                        $programs[$key][] = "<p>{$k} ".implode(", ", $links)."</p>";
                    }
                    else{
                        $programs[$key][] = "<p><a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=Programs/{$p}' target='_blank'>{$k}</a></p>";
                    }
                }
                foreach($topic['community'] as $k => $p){
                    $k = preg_replace("/(.*(^|→|↴))(?!.*(→|↴))(.*)/", "$1<a style='vertical-align:top;' target='_blank' href='{$wgServer}{$wgScriptPath}/index.php/Special:PharmacyMap#/{$p}'>$4</a>", $k);
                    $k = str_replace("→", "</span>→<span class='cb'>", $k);
                    $k = str_replace("↴", "</span>↴<span class='cb' style='width: 100%; text-align: right;'>", $k);
                    $community[$key][] = "<p>{$k}</p>";
                }
            }
            else{
                $nos[$key] = $topic['no'];
            }
        }
        
        //Category
        $borderBottom = (count($subTopics) == 0) ? "" : "border-bottom-style: dashed;";
        $html = "<tr>
                    <td align='left' style='font-style: initial; font-size: 1.2em; {$borderBottom}'>
                        <span class='AVOID {$comp}'>".substr(ActionPlan::comp2Text($comp, $wgLang->getCode()), 0, 1)."</span><span class='AVOIDrest'>".substr(ActionPlan::comp2Text($comp, $wgLang->getCode()), 1)."</span>
                    </td>";
        $html .= (!isset($nos[ActionPlan::comp2Text($comp)])) 
               ? "<td align='center' style='font-size: 0.9em; {$borderBottom}'>".@implode("\n", $education[ActionPlan::comp2Text($comp)])."</td>
                  <td align='center' style='font-size: 0.9em; {$borderBottom}'>".@implode("\n", $programs[ActionPlan::comp2Text($comp)])."</td>
                  <td style='font-size: 0.9em; {$borderBottom}'>".@implode("\n", $community[ActionPlan::comp2Text($comp)])."</td>"
               : "<td style='{$borderBottom}' colspan='3'>{$nos[ActionPlan::comp2Text($comp)]}</td>";
        $html .= "</tr>";
        
        // SubTopics
        foreach($subTopics as $key => $topic){
            $borderBottom = ($topic == array_values($subTopics)[count($subTopics)-1]) ? "" : "border-bottom-style: dashed;";
            $html .= "<tr>
                        <td align='center' style='font-style: initial; font-size: 0.9em;{$borderBottom}'>{$topic}</td>";
            $html .= (!isset($nos[$key])) 
                   ? "<td align='center' style='font-size: 0.9em; {$borderBottom}'>".@implode("\n", $education[$key])."</td>
                      <td align='center' style='font-size: 0.9em; {$borderBottom}'>".@implode("\n", $programs[$key])."</td>
                      <td style='font-size: 0.9em; {$borderBottom}'>".@implode("\n", $community[$key])."</td>"
                   : "<td style='{$borderBottom}' colspan='3'>{$nos[$key]}</td>";
            $html .= "</tr>";
        }
        return $html;
    }
    
    function generateReport($person){
        global $wgServer, $wgScriptPath, $config, $wgLang;
        $dir = dirname(__FILE__) . '/';
        require_once($dir . '/../../../../../Classes/SmartDomDocument/SmartDomDocument.php');
        $api = new UserFrailtyIndexAPI();
        $reportType = (isset($_GET['reportType'])) ? $_GET['reportType'] : "RP_AVOID";
        $scores = $api->getFrailtyScore($person->getId(), $reportType);

        $margins = array('top'     => 1,
                         'right'   => 1,
                         'bottom'  => 1,
                         'left'    => 1);

        $pdfNoDisplay = (!isset($_GET['preview'])) ? ".pdfnodisplay { display:none }" : "";
        $bodyMargins = (!isset($_GET['preview'])) ? "margin-top: {$margins['top']}cm;
                                                     margin-right: {$margins['right']}cm;
                                                     margin-bottom: {$margins['bottom']}cm;
                                                     margin-left: {$margins['left']}cm;" : "margin: 0;";
        $bodyPadding = (!isset($_GET['preview'])) ? "" : "padding-top: {$margins['top']}cm;
                                                          padding-right: {$margins['right']}cm;
                                                          padding-bottom: {$margins['bottom']}cm;
                                                          padding-left: {$margins['left']}cm;";

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
                            }
                            
                            body {
                                {$bodyMargins}
                                font-family: 'Nunito Sans';
                                font-weight: 600;
                                line-height: 1em;
                            }
                            
                            div.stickyContainer {
                                position: absolute;
                                bottom: 0;
                                left: 1cm;
                                right: 1cm;
                                width: auto;
                            }
                            
                            table.sticky {
                                transform-origin: top left;
                                z-index: 1000;
                                position: sticky;
                                top: 0;
                            }
                            
                            div.body {
                                transform-origin: top left;
                                {$bodyPadding}
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
                                
                                margin-top: 2.25em;
                                margin-right: 1.5em;
                            }
                            
                            .title {
                                font-weight: 700;
                                font-size: 1.6em;
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
                                height: 30px;
                                display: inline-block;
                            }
                            
                            table p {
                                margin-top: 0;
                                margin-bottom: 0.75em;
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
                            
                            .AVOID {
                                border-radius: 10em;
                                font-size: 1.5em;
                                width: 1.5em;
                                display: inline-block;
                                height: 1.5em;
                                text-align: center;
                                line-height: 1.5em;
                                font-weight: bold;
                                color: white;
                                margin: 0.1em;
                                margin-left: 0;
                            }
                            
                            .AVOIDrest {
                                display: inline-block; 
                                width: 3em; 
                                vertical-align: text-top;
                            }

                            .AVOID.A {
                                background: #c7db55;
                            }

                            .AVOID.V {
                                background: #2cace2;
                            }

                            .AVOID.O {
                                background: #f79234;
                            }

                            .AVOID.I {
                                background: #3ab094;
                            }

                            .AVOID.D {
                                background: #4b8ecc;
                            }

                            .AVOID.S {
                                background: #71ad94;
                            }

                            .AVOID.F {
                                background: #9669ba;
                            }";
                    if(!isset($_GET['preview'])){
                        $html .= ".AVOID {
                            display: inline-block;
                            line-height: 1em;
                            margin-top: 0.5em;
                        }
                        
                        .AVOIDrest {
                            vertical-align: middle;
                            height: 1em;
                            margin-top: -0.7em;
                        }";
                    }
                    if($wgLang->getCode() == "en"){
	                    $html .= "fr, .fr { display: none !important; }";
	                }
	                else if($wgLang->getCode() == "fr"){
	                    $html .= "en, .en { display: none !important; }";
	                }
                    $html .= "     
                        </style>
                    </head>
                    <body>
                        <div class='stickyContainer pdfnodisplay'>
                            <table class='sticky recommendations' cellspacing='0' style='width: 100%;'>
                                <tr>
                                    <th style='min-width: 9em; width: 9em; padding-bottom: 0; position: relative;'>
                                        <div style='line-height: 1em; position: absolute; top: 8px; left: 0;width:100%; text-align: center;'>
                                            <en>Category</en>
                                            <fr>Catégorie</fr>
                                        </div>
                                    </th>
                                    <th align='center' style='width: 6.5em;'>
                                        <en>Education</en>
                                        <fr>Éducation</fr>
                                    </th>
                                    <th align='center' style='width: 9em;'>
                                        <en>AVOID Programs</en>
                                        <fr>Programmes Proactif</fr>
                                    </th>
                                    <th align='center' style='width: 13em;'>
                                        <en>Community Programs</en>
                                        <fr>Ressources Communautaires</fr>
                                    </th>
                                </tr>
                            </table>
                        </div>
                        <div class='body'>
                        <img src='{$wgServer}{$wgScriptPath}/skins/bg_top.png' style='z-index: -2; position: absolute; top:0; left: 0; right:0; width: 216mm;' />
                        <div class='logos'>
                            <img src='{$wgServer}{$wgScriptPath}/skins/logo3.png' />
                            <img style='max-height: 100px;' src='{$wgServer}{$wgScriptPath}/skins/logo2.png' />
                            <en><img src='{$wgServer}{$wgScriptPath}/skins/logo1.png' /></en><fr><img src='{$wgServer}{$wgScriptPath}/skins/logo1fr.png' /></fr>
                        </div>
                        <div class='title-box'>
                            <div class='title'>
                                <en>My Frailty and Behavioural Status:<br />Report from Healthy Aging Assessment</en>
                                <fr>Mon état de fragilité:<br />Rapport de l’évaluation du vieillissement sain</fr>
                            </div>
                            <div class='frailtyStatus'>
                                <en>My Frailty Status: <u>{$scores['Label']}</u></en>
                                <fr>Mon état de fragilité: <u>{$scores["LabelFr"]}</u></fr>
                            </div>
                            <div class='pdfnodisplay' style='margin-top:1em;'>
                                <en>
                                    Your recommendations with direct links to resources are below.<br />
                                    You can also print your personal report <a href='{$wgServer}{$wgScriptPath}/index.php/Special:FrailtyReport?reportType={$reportType}' target='_blank'><b><u>here</u></b></a>.
                                </en>
                                <fr>
                                    Vos recommandations et les liens qui mènent vers les ressources se trouvent ci-dessous.<br />
                                    Vous pouvez également faire imprimer votre rapport <a href='{$wgServer}{$wgScriptPath}/index.php/Special:FrailtyReport?reportType={$reportType}' target='_blank'><b><u>ici</u></b></a>.
                                </fr>
                            </div>
                        </div>
                        <div class='list'>
                            <p><img class='li' src='{$wgServer}{$wgScriptPath}/skins/li.png' /><en>This report shows the items that went into your frailty status. Where a need was identified from your answers, some recommended resources appear in that topic to address that specific item. If you do not see any recommendations, it means that no needs were identified from your answers.</en><fr>Ce rapport montre les domaines évalués pour mesurer votre état de fragilité. Lorsqu’un besoin est établi à partir de vos réponses, certaines ressources recommandées apparaissent dans cette rubrique concernant ce domaine précis. Si vous ne voyez pas de recommandations, cela signifie qu’aucun besoin n’a été établi à partir de vos réponses.</fr>
                            </p>
                        </div>
                        
                        <script type='text/php'>
                            \$php_code = '
                                if(\$PAGE_NUM == 1){
                                    \$note = \"{$person->getNameForForms()}\";
                                    \$font = \$fontMetrics->getFont(\"verdana\");
                                    \$size = 6;
                                    \$text_height = \$fontMetrics->getFontHeight(\$font, \$size);
                                    \$text_width = \$fontMetrics->getTextWidth(\"\$note\", \$font, \$size);
                                    \$color = array(0,0,0);
                                    \$w = \$pdf->get_width();
                                    \$h = \$pdf->get_height();
                                    \$y = \$h - \$text_height - 24;

                                    \$x = \$pdf->get_width() - \$text_width;

                                    \$pdf->text(\$x - 28, \$y+(\$text_height) - \$text_height + 4, \"\$note\", \$font, \$size, \$color);
                                }
                                ';
                             \$pdf->page_script(\$php_code);
                        </script>
                        
                        <table class='recommendations' cellspacing='0' style='width: 100%;'>
                            <tr>
                                <th style='min-width: 9em; width: 9em; padding-bottom: 0; position: relative;'>
                                    <div style='line-height: 1em; position: absolute; top: 8px; left: 0;width:100%; text-align: center;'><en>Category</en><fr>Catégorie</fr></div>
                                </th>
                                <th align='center' style='width: 6.5em;'>
                                    <en>Education</en>
                                    <fr>Éducation</fr>
                                </th>
                                <th align='center' style='width: 9em;'>
                                    <en>AVOID Programs</en>
                                    <fr>Programmes Proactif</fr>
                                </th>
                                <th align='center' style='width: 13em;'>
                                    <en>Community Programs</en>
                                    <fr>Ressources Communautaires</fr>
                                </th>
                            </tr>";
        foreach(self::$rows as $comp => $topics){
            $html .= $this->drawRow($comp, $topics, $scores);
        }
        
        $html .= "</table>";
                        
        $otherHTML = "";
        foreach(self::$otherRows as $key => $row){
            if(@$scores[$key] > 0 || @$scores["Behavioral"][$key] > 0){
                $otherHTML .= "<div style='display:inline-block; width: 50%; margin-bottom: 0.25em;'>
                                 <img src='{$wgServer}{$wgScriptPath}/extensions/Reporting/Report/SpecialPages/AVOID/images/{$row['img']}' style='width:1.25em; vertical-align: middle;' />
                                 <span style='vertical-align:middle;'>";
                $otherHTML .= (count($row['community'])) 
                            ? "<a target='_blank' href='{$wgServer}{$wgScriptPath}/index.php/Special:PharmacyMap#/".array_values($row['community'])[0]."'>{$row['text']}</a>"
                            : $row['text'];
                $otherHTML .= "  </span>
                               </div>";
            }
        }
        
        if($otherHTML != ""){
            $html .= "<p><b><en>Your frailty score also considers the following:</en><fr>Votre score de fragilité tient également compte de ce qui suit :</fr></b></p>{$otherHTML}";
        }
        
        $html .= "<p><img class='li' src='{$wgServer}{$wgScriptPath}/skins/li.png' /><en>While the AVOID Frailty program has some resources to target these risks, talking to your healthcare provider will provide you with a personal course of action. Click on a topic of concern for further education or community programs that can help you.</en><fr>Même si le programme Proactif vous offre des ressources à propos de ces risques, il ne remplace en rien une consultation auprès de votre spécialiste de la santé qui peut mettre en place un plan d’action personnalisé à votre état de santé. Cliquez sur l’un des domaines pour avoir accès à des modules éducatifs et à des ressources locales pratiques.</fr>
                        </p>
                        <p><img class='li' src='{$wgServer}{$wgScriptPath}/skins/star.png' /><en>The recommendations throughout this program are meant to support healthy 
behaviour. They are not clinical recommendations, for which you should seek advice from your health care providers (example: doctor, pharmacist, dentist).</en><fr>Les recommandations formulées tout au long de ce programme visent à favoriser un vieillissement en santé. Il ne s’agit pas de recommandations cliniques pour lesquelles vous devez demander conseil à un professionnel de la santé (p. ex. médecin, pharmacien, dentiste).</fr></p>";
        
        // Selected for you
        $selectedHTML = "";
        
        $blob = new ReportBlob(BLOB_TEXT, YEAR, $person->getId(), 0);
        $blob_address = ReportBlob::create_address("RP_AVOID", "AVOID_Questions_tab0", "income_avoid", 0);
        $blob->load($blob_address);
        $income = $blob->getData();
        
        $blob = new ReportBlob(BLOB_TEXT, YEAR, $person->getId(), 0);
        $blob_address = ReportBlob::create_address("RP_AVOID", "AVOID_Questions_tab0", "transportation_avoid", 0);
        $blob->load($blob_address);
        $transportation = $blob->getData();
        if($income == "Under $10,000" ||
           $income == "$10,000 to $24,999" ||
           $income == "$25,000 to $49,999"){
            $selectedHTML .= "<en><a target='_blank' href='https://docs.google.com/document/d/1guEL1L_062NgbsAP9u5tebnp4vc7Zpo9/edit?usp=drive_link&ouid=105295311895815272560&rtpof=true&sd=true'>Practical Assistance</a><br /></en>";
        }
        
        if($transportation == "Taxi or similar paid services" ||
           $transportation == "Passenger in a motor vehicle"){
            $selectedHTML .= "<en><a target='_blank' href='https://docs.google.com/presentation/d/1iYy86czxJABOxpm7gC5LZdhr025C-pwK/edit?usp=sharing&ouid=109660911800093799145&rtpof=true&sd=true'>Transit Travel Training</a><br /></en>
                              <fr><a target='_blank' href=\"{$wgServer}{$wgScriptPath}/EducationModules/STTR-Guide d'utilisation.pdf\">Guide d’utilisation de la Société de Transport de Trois-Rivières</a><br /></fr>";
        }
        
        if($selectedHTML != ""){
            $html .= "<p><en>Selected for you</en><fr>Pour vous</fr><br />{$selectedHTML}</p>";
        }
        
        // Footer
        $html .= "<div style='width:100%; text-align:center;'>
                            <en><a href='https://HealthyAgingCentres.ca' target='_blank'>HealthyAgingCentres.ca</a></en>
                            <fr><a href='https://Proactifquebec.ca' target='_blank'>Proactifquebec.ca</a></fr>
                        </div>
                        <br /><br /><br /><br /><br />
                        <img src='{$wgServer}{$wgScriptPath}/skins/bg_bottom.png' style='z-index: -2; position: absolute; bottom:0; left: 0; right:0; width: 216mm;' />
                        <script type='text/javascript'>
                            var initialWidth = $(window).width();
                            var wgLang = '{$wgLang->getCode()}';
                            $(document).ready(function(){
                                $(window).resize(function(){
                                    $('html').width('100%');
                                    var desiredWidth = $(window).width();
                                    $('html').width('216mm');
                                    var scaleFactor = desiredWidth/initialWidth;
                                    $('div.body').css('transform', 'scale(' + scaleFactor + ')');
                                    $('div.stickyContainer').css('top', 397*scaleFactor);
                                    if(wgLang == 'fr'){
                                        //$('div.stickyContainer').css('top', parseFloat($('div.stickyContainer').css('top')) + 10*scaleFactor);
                                    }
                                    $('table.sticky').css('transform', 'scale(' + scaleFactor + ')')
                                                     .css('margin-left', scaleFactor - 1 + 'cm');
                                    $('body').height($('div.body').outerHeight()*scaleFactor);
                                }).resize();
                            });
                        </script>
                        </div>
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
        Gamification::log('OpenReport');
        return $html;
    }
    
    function execute($par){
        global $wgServer, $wgScriptPath, $dompdfOptions, $wgOut;
        $dir = dirname(__FILE__);
        require_once($dir . '/../../../../../config/dompdf_config.inc.php');
        $me = Person::newFromWgUser();
        $person = $me;
        if(isset($_GET['user'])){
            $person = Person::newFromId($_GET['user']);
        }
        if($person->getId() == 0){
            echo $wgOut->addHTML("This user does not exist.");
            return;
        }
        $html = "";
        if(!($me->isRoleAtLeast(STAFF) || $person->getId() == $me->getId())){
            $found = false;
            $rels = $me->getRelations("Assesses");
            foreach($rels as $rel){
                $found = ($found || ($rel->getUser2()->getId() == $person->getId()));
            }
            if(!$found){
                echo $wgOut->addHTML("You do not have permission to view this user.");
                return;
            }
        }
        
        $html = $this->generateReport($person);
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
