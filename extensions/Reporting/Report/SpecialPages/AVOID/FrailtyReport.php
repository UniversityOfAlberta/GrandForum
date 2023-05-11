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
            "text" => "<en>Nutritional Status</en><fr>État nutritionnel</fr>",
            "no" => "<en>
                        Great job, keep it up!<br />
                        Protein, calcium and vitamin D are particularly important to maintain strong bones and muscles.
                     </en>
                     <fr>
                        Bon travail, continuez!<br />
                        Les protéines, le calcium et la vitamine D sont particulièrement importants pour maintenir des os sains et des muscles forts. 
                     </fr>",
            "education" => array(
                "<en>Diet and Nutrition</en><fr>Alimentation</fr>" => "DietAndNutrition"
            ),
            "programs" => array(
                "<en>Peer Coaching</en><fr>Coaching par les pairs</fr>" => "PeerCoaching"
            ),
            "community" => array(
                "<en>Diet and Nutrition</en><fr>Alimentation</fr>" => "CFN-DIET",
                "<en>Activity</en><fr>Activité physique</fr> → <en>Exercise</en><fr>Exercice</fr>" => "CFN-ACT-EX"
            )
        ),
        "Oral Health" => array(
            "img" => "dental-care.png",
            "text" => "<en>Oral Health</en><fr>Santé bucco-dentaire</fr>",
            "no" => "<en>
                        Way to go! <br />
                        Good oral hygiene practices can help you to avoid tooth pain that makes it difficult to eat. Make sure you continue with regular visits to your dentist.
                     </en>
                     <fr>
                        Bravo!<br />
                        De bonnes pratiques d’hygiène buccale peuvent vous aider à éviter les douleurs dentaires qui nuisent à l’alimentation. Veillez à visiter régulièrement votre dentiste.
                     </fr>",
            "education" => array(
                "<en>Diet and Nutrition</en><fr>Alimentation</fr>" => "DietAndNutrition"
            ),
            "programs" => array(
            
            ),
            "community" => array(
                "<en>Diet and Nutrition</en><fr>Alimentation</fr>" => "CFN-DIET"
            )
        ),
        "Fatigue" => array(
            "img" => "tiredness.png",
            "text" => "<en>Fatigue</en><fr>Fatigue</fr>",
            "no" => "<en>
                        Getting older means your sleep will change, but not necessarily impact your daily living. It looks like you’ve got this under control!
                     </en>
                     <fr>
                        En vieillissant, votre sommeil va changer, mais cela n’aura pas nécessairement d’impact sur votre vie quotidienne. On dirait que tout va bien!
                     </fr>",
            "education" => array(
                "<en>Sleep</en><fr>Sommeil</fr>" => "Sleep"
            ),
            "programs" => array(
                "<en>Peer Coaching</en><fr>Coaching par les pairs</fr>" => "PeerCoaching",
                "<en>Community Connectors</en><fr>Connecteurs communautaires</fr>" => "CommunityConnectors"
            ),
            "community" => array(
                "<en>Activity</en><fr>Activité physique</fr> → <en>Exercise</en><fr>Exercice</fr> ↴ <en>Movement and Mindfulness</en><fr>Mouvement et pleine conscience</fr>" => "CFN-ACT-EX-MOV",
                "<en>Activity</en><fr>Activité physique</fr> → <en>Sleep</en><fr>Sommeil</fr>" => "CFN-ACT-SLEEP",
                "<en>Activity</en><fr>Activité physique</fr> → <en>Exercise</en><fr>Exercice</fr>" => "CFN-ACT-EX"
            )
        ),
        "Pain" => array(
            "img" => "back.png",
            "text" => "<en>Pain</en><fr>Douleur</fr>",
            "no" => "<en>
                        If you’re not experiencing pain or discomfort, that’s great! Age isn’t synonymous with aches and pains, but by the looks of things, you already know that!
                     </en>
                     <fr>
                        Si vous ne ressentez aucune douleur ou gêne, c’est très bien! L’âge n’est pas synonyme de courbatures, mais on dirait que vous le savez déjà! 
                     </fr>",
            "education" => array(
                "<en>Activity</en><fr>Activité physique</fr>" => "Activity"
            ),
            "programs" => array(
                "<en>Peer Coaching</en><fr>Coaching par les pairs</fr>" => "PeerCoaching"
            ),
            "community" => array(
                "<en>Chronic Condition</en><fr>Maladies chroniques</fr> → <en>Chronic<br />Pain</en><fr>Douleur chronique</fr>" => "CFN-CHRONIC-PAIN",
                "<en>Activity</en><fr>Activité physique</fr>" => "CFN-ACT"
            )
        ),
        "Physical Activity" => array(
            "img" => "physical-activity.png",
            "text" => "<en>Physical Activity</en><fr>Activité physique</fr>",
            "no" => "<en>
                        You are extending independent living, reducing your risk of many chronic conditions, and reducing your risk of falls. Great job!
                     </en>
                     <fr>
                        Vous prolongez votre autonomie, réduisez votre risque de souffrir de nombreuses maladies chroniques et diminuez votre risque de chute. Bon travail!
                     </fr>",
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
        "Strength" => array(
            "img" => "muscle.png",
            "text" => "<en>Strength</en><fr>Force physique</fr>",
            "no" => "<en>
                        Keep up your strength! We naturally lose muscle tone as we age, so it’s great you’re combating that now!
                     </en>
                     <fr>
                        La force n’a plus de secret pour vous! Nous perdons naturellement du tonus musculaire en vieillissant, c’est donc une bonne chose que vous preniez les moyens pour le conserver maintenant!
                     </fr>",
            "education" => array(
                "<en>Activity</en><fr>Activité physique</fr>" => "Activity"
            ),
            "programs" => array(
                "<en>Peer Coaching</en><fr>Coaching par les pairs</fr>" => "PeerCoaching"
            ),
            "community" => array(
                "<en>Activity</en><fr>Activité physique</fr> → <en>Exercise</en><fr>Exercice</fr> → <en>Fitness</en><fr>Conditionnement physique</fr>" => "CFN-ACT-EX-FIT"
            )
        ),
        "Walking Speed" => array(
            "img" => "marathon.png",
            "text" => "<en>Walking Speed</en><fr>Vitesse de marche</fr>",
            "no" => "<en>
                        Walking is a great exercise you can continue to do as you age. It’s great for strength, mobility and reducing your risk of falling. Sounds like you aren’t slowing down, keep it up!
                     </en>
                     <fr>
                        La marche est un excellent exercice que vous pouvez continuer à pratiquer en vieillissant. C’est une excellente activité pour maintenir votre force et votre mobilité tout en réduisant le risque de chute. Rien ne vous arrête, continuez comme ça!
                     </fr>",
            "education" => array(
                "<en>Activity</en><fr>Activité physique</fr>" => "Activity"
            ),
            "programs" => array(
            
            ),
            "community" => array(
                "<en>Activity</en><fr>Activité physique</fr> → <en>Exercise</en><fr>Exercice</fr>" => "CFN-ACT-EX",
                "<en>Activity</en><fr>Activité physique</fr> → <en>Exercise</en><fr>Exercice</fr> ↴ <en>Movement and Mindfulness</en><fr>Mouvement et pleine conscience</fr>" => "CFN-ACT-EX-MOV",
                "<en>Transportation</en><fr>Transport</fr> → <en>Driving<br />Programs</en><fr>Programmes<br />d’accompagnement</fr>" => "CFN-TRANSPORT-DRIVP"
            )
        ),
        "Falls and Balance" => array(
            "img" => "falling.png",
            "text" => "<en>Falls and Balance</en><fr>Chutes et équilibre</fr>",
            "no" => "<en>
                        Did you know that every 12 seconds a Canadian aged 65years+ experiences a fall? Sounds like you’ve reduced your risk. Way to go!
                     </en>
                     <fr>
                        Saviez-vous que toutes les 12 secondes, un Canadien ou une Canadienne de 65 ans et plus fait une chute? On dirait que vous avez réduit vos risques. Bravo! 
                     </fr>",
            "education" => array(
                "<en>Falls Prevention</en><fr>Prévention des chutes</fr>" => "FallsPrevention",
                "<en>Activity</en><fr>Activité physique</fr>" => "Activity"
            ),
            "programs" => array(
                "<en>Peer Coaching</en><fr>Coaching par les pairs</fr>" => "PeerCoaching"
            ),
            "community" => array(
                "<en>Home & Care Partners</en><fr>Services de soutien<br />et de soins à domicile</fr> ↴ <en>Help at Home</en><fr>Aide à domicile</fr>" => "CFN-HOMECARE-HELP",
                "<en>Activity</en><fr>Activité physique</fr> → <en>Exercise</en><fr>Exercice</fr> ↴ <en>Movement and Mindfulness</en><fr>Mouvement et pleine conscience</fr>" => "CFN-ACT-EX-MOV",
                "<en>Transportation</en><fr>Transport</fr> → <en>Driving<br />Programs</en><fr>Programmes<br />d’accompagnement</fr>" => "CFN-TRANSPORT-DRIVP"
            )
        ),
        "Urinary Continence" => array(
            "img" => "urinary-tract.png",
            "text" => "<en>Urinary Continence</en><fr>Continence urinaire</fr>",
            "no" => "<en>
                        You are doing well. Maintaining a healthy lifestyle is an important strategy to avoid incontinence. If you do develop problems, see your healthcare provider since incontinence is not an inevitable part of aging and there are measures that can be taken to help.
                     </en>
                     <fr>
                        Vous vous débrouillez bien. Le maintien d’un mode de vie sain est une stratégie importante pour éviter l’incontinence. Si vous rencontrez des problèmes, consultez votre professionnel de la santé, car il y a des moyens d’éviter l’incontinence en vieillissant.
                     </fr>",
            "education" => array(
                "<en>Activity</en><fr>Activité physique</fr>" => "Activity"
            ),
            "programs" => array(
                "<en>Peer Coaching</en><fr>Coaching par les pairs</fr>" => "PeerCoaching"
            ),
            "community" => array(
                "<en>Healthcare Services</en><fr>Services de santé</fr> → <en>Clinics</en><fr>Cliniques</fr>" => "CFN-HEALTH-CLINICS",
                "<en>Activity</en><fr>Activité physique</fr> → <en>Exercise</en><fr>Exercice</fr>" => "CFN-ACT-EX"
            )
        ),
        "Memory" => array(
            "img" => "memory.png",
            "text" => "<en>Memory</en><fr>Mémoire</fr>",
            "no" => "<en>
                        You are doing well. Just as exercise is important to keep muscle strength, doing activities which require mental effort are important for brain function to be maintained.
                     </en>
                     <fr>
                        Vous vous débrouillez bien. Tout comme l’exercice est important pour conserver la force musculaire, les activités qui demandent un effort mental sont importantes pour le maintien des fonctions cérébrales.
                     </fr>",
            "education" => array(
                "<en>Activity</en><fr>Activité physique</fr>" => "Activity",
                "<en>Interact</en><fr>Vie sociale</fr>" => "Interact"
            ),
            "programs" => array(
                "<en>Community Connectors</en><fr>Connecteurs communautaires</fr>" => "CommunityConnectors"
            ),
            "community" => array(
                "<en>Interact</en><fr>Vie sociale</fr>" => "CFN-INT",
                "<en>Activity</en><fr>Activité physique</fr>" => "CFN-ACT"
            )
        ),
        "Mental Health" => array(
            "img" => "mental-health.png",
            "text" => "<en>Mental Health</en><fr>Santé mentale</fr>",
            "no" => "<en>
                        Mental Health problems are prevalent at every age. If your mental health status changes, speak with your family doctor.
                     </en>
                     <fr>
                        Les problèmes de santé mentale sont répandus à tout âge. Si votre état de santé mentale change, parlez-en à votre médecin de famille. 
                     </fr>",
            "education" => array(
                "<en>Interact</en><fr>Vie sociale</fr>" => "Interact",
                "<en>Activity</en><fr>Activité physique</fr>" => "Activity"
            ),
            "programs" => array(
                "<en>Community Connectors</en><fr>Connecteurs communautaires</fr>" => "CommunityConnectors",
                "<en>Peer Coaching</en><fr>Coaching par les pairs</fr>" => "PeerCoaching"
            ),
            "community" => array(
                "<en>Interact</en><fr>Vie sociale</fr>" => "CFN-INT",
                "<en>Activity</en><fr>Activité physique</fr>" => "CFN-ACT",
                "<en>Mental Health</en><fr>Santé mentale</fr>" => "CFN-MH"
            )
        ),
        "Multiple Medications" => array(
            "img" => "syringe.png",
            "text" => "<en>Multiple Medications</en><fr>Médicaments multiples</fr>",
            "no" => "<en>
                        Continue to conduct annual medication reviews with your doctor or pharmacist. These reviews could result in reducing a dose, changing medications or stopping an unnecessary medication.
                     </en>
                     <fr>
                        Continuez à faire des bilans annuels de vos médicaments avec votre médecin ou votre pharmacien. Ces examens pourraient aboutir à la réduction d’une dose, à un changement de médicament ou à l’arrêt d’un médicament inutile. 
                     </fr>",
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
        ),
        "Health Conditions" => array(
            "img" => "medical-chechup.png",
            "text" => "<en>Health Conditions</en><fr>Affections de santé</fr>",
            "no" => "",
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
                "<en>Chronic Conditions</en><fr>Maladies chroniques</fr>" => "CFN-CHRONIC",
                "<en>Diet and Nutrition</en><fr>Alimentation</fr>" => "CFN-DIET",
                "<en>Activity</en><fr>Activité physique</fr>" => "CFN-ACT"
            )
        ),
        "Self-Perceived Health" => array(
            "img" => "fever.png",
            "text" => "<en>Self-Perceived Health</en><fr>État de santé autoévalué</fr>",
            "no" => "",
            "education" => array(
                
            ),
            "programs" => array(
                "<en>Peer Coaching</en><fr>Coaching par les pairs</fr>" => "PeerCoaching"
            ),
            "community" => array(
            
            )
        ),
        "Sensory: Hearing and Vision" => array(
            "img" => "sensory.png",
            "text" => "<en>Sensory: Hearing and Vision</en><fr>Sensorielle : Audition et vision</fr>",
            "no" => "<en>
                        If anything changes with vision or hearing, visit your appropriate health care provider.
                     </en>
                     <fr>
                        En cas de changement dans votre vision ou audition, consultez un professionnel de la santé. 
                     </fr>",
            "education" => array(
                
            ),
            "programs" => array(

            ),
            "community" => array(
                "<en>Disability Services</en><fr>Services liés à l’incapacité</fr>" => "CFN-DIS",
                "<en>Home & Care Partners</en><fr>Services de soutien<br />et de soins à domicile</fr> ↴ <en>Help at Home</en><fr>Aide à domicile</fr>" => "CFN-HOMECARE-HELP",
                "<en>Transportation</en><fr>Transport</fr> → <en>Driving<br />Programs</en><fr>Programmes<br />d’accompagnement</fr>" => "CFN-TRANSPORT-DRIVP"
            )
        )
    );
    
    static $behavioralRows = array(
        "Activity" => array(
            "img" => "Activity.png",
            "text" => "<en>Activity</en><fr>Activité physique</fr>",
            "no" => "<en>
                        Way to stay active! If you want to learn more, you can view the Activity module in education resources or any activity-related webinar within Cyber-Seniors.
                     </en>
                     <fr>
                        Bonne manière de rester actif! Si vous souhaitez en savoir plus, vous pouvez consulter le module Activité physique dans les ressources éducatives ou toute autre ressource offerte pour ce domaine dans le programme.
                     </fr>",
            "education" => array(
                "<en>Activity</en><fr>Activité physique</fr>" => "Activity"
            ),
            "programs" => array(
                "<en>Peer Coaching</en><fr>Coaching par les pairs</fr>" => "PeerCoaching"
            ),
            "community" => array(
                "<en>Activity</en><fr>Activité physique</fr>" => "CFN-ACT",
                "<en>Interact</en><fr>Vie sociale</fr>" => "CFN-INT"
            )
        ),
        "Vaccination" => array(
            "img" => "Vaccination.png",
            "text" => "<en>Vaccination</en><fr>Vaccination</fr>",
            "no" => "<en>
                        As we age, it’s harder to recover from infectious diseases like the flu, pneumonia, and COVID-19. Way to keep up with your vaccines!
                     </en>
                     <fr>
                        En vieillissant, il est plus difficile de se remettre de maladies infectieuses comme la grippe, la pneumonie et la COVID-19. C’est une bonne façon de faire le suivi de vos vaccins!
                     </fr>",
            "education" => array(
                "<en>Vaccination</en><fr>Vaccination</fr>" => "Vaccination"
            ),
            "programs" => array(
                "<en>Peer Coaching</en><fr>Coaching par les pairs</fr>" => "PeerCoaching"
            ),
            "community" => array(
                "<en>Vaccination/Optimize Medication</en><fr>Vaccination et optimisation des médicaments</fr>" => "CFN-VAC",
                "<en>Transportation</en><fr>Transports</fr>" => "CFN-TRANSPORT"
            )
        ),
        "Optimize Medication" => array(
            "img" => "OptimizeMedication.png",
            "text" => "<en>Optimize Medication</en><fr>Optimisation des médicaments</fr>",
            "no" => "<en>
                        It’s great that you feel like you have your medications under control. If you need help with managing the medications you are on, visit the following Cyber-Seniors Webinars: How to Fill Prescriptions Online, Medisafe Pill Reminder App
                     </en>
                     <fr>
                        C’est bien que vous ayez l’impression d’avoir le contrôle sur vos médicaments. Si vous avez besoin d’aide pour les gérer, consultez les ressources sur l’optimisation des médicaments.
                     </fr>",
            "education" => array(
                 "<en>Optimize Medication</en><fr>Optimisation des médicaments</fr>" => "OptimizeMedication"
            ),
            "programs" => array(
                "<en>Peer Coaching</en><fr>Coaching par les pairs</fr>" => "PeerCoaching"
            ),
            "community" => array(
                "<en>Vaccination/Optimize Medication</en><fr>Vaccination et optimisation des médicaments</fr>" => "CFN-VAC",
                "<en>Activity</en><fr>Activité physique</fr> → <en>Exercise</en><fr>Exercice</fr>" => "CFN-ACT-EX",
                "<en>Interact</en><fr>Vie sociale</fr>" => "CFN-INT"
            )
        ),
        "Interact" => array(
            "img" => "Interact.png",
            "text" => "<en>Interact</en><fr>Vie sociale</fr>",
            "no" => "<en>
                        Keep up with your social interactions. It provides you with mental and physical health benefits!<br />
                        If you want to learn more, you can view the Interact module in education resources
                     </en>
                     <fr>
                        Maintenez vos interactions sociales.  Elles vous procurent des bienfaits mentaux et physiques!<br />
                        Si vous souhaitez en savoir plus, vous pouvez consulter le module « Vie sociale » dans les modules d’éducation.
                     </fr>",
            "education" => array(
                "<en>Interact</en><fr>Vie sociale</fr>" => "Interact"
            ),
            "programs" => array(
                "<en>Peer Coaching</en><fr>Coaching par les pairs</fr>" => "PeerCoaching",
                "<en>Community Connectors</en><fr>Connecteurs communautaires</fr>" => "CommunityConnectors"
            ),
            "community" => array(
                "<en>Interact</en><fr>Vie sociale</fr>" => "CFN-INT",
                "<en>Activity</en><fr>Activité physique</fr>" => "CFN-ACT"
            )
        ),
        "Diet and Nutrition" => array(
            "img" => "DietAndNutrition.png",
            "text" => "<en>Diet and Nutrition</en><fr>Alimentation</fr>",
            "no" => "<en>
                        Great job, keep it up! <br />
                        Continue to incorporate protein, calcium and vitamin D  - to maintain strong bones and muscles.<br />
                        <br />
                        If you want to learn more about diet and nutrition, you can view the Nutrition & Diet education module, or any nutrition-related webinar within Cyber-Seniors.
                     </en>
                     <fr>
                        Bon travail, continuez!<br />
                        Continuez d’intégrer des protéines, du calcium et de la vitamine D à votre alimentation pour avoir des os et des muscles en bonne santé.<br />
                        Si vous souhaitez en savoir plus sur les régimes alimentaires et la nutrition, vous pouvez consulter le module Activité physique dans les ressources éducatives ou toute autre ressource sur l’alimentation offerte dans le programme.
                     </fr>",
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
        $need = "<en>N</en><fr>N</fr>";
        $education = "";
        $programs = "";
        $community = "";
        if($scores[$key] > 0){
            $need = "<en>Y</en><fr>O</fr>";
            foreach($row['education'] as $k => $e){
                if(is_array($e)){
                    $links = array();
                    foreach($e as $k1 => $e1){
                        $links[] = "<a href='{$e1}' target='_blank'>{$k1}</a>";
                    }
                    $education .= "<p>{$k} ".implode(", ", $links)."</p>";
                }
                else{
                    $education .= "<p><a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=EducationModules/{$e}' target='_blank'>{$k}</a></p>";
                }
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
                $community .= "<p>{$k}</p>";
            }
        }
        $html = "<tr>
                    <td align='center' style='padding-top: 1em; font-style: initial;'>{$need}</td>
                    <td align='center'><img src='{$wgServer}{$wgScriptPath}/extensions/Reporting/Report/SpecialPages/AVOID/images/{$row['img']}' alt='{$key}' /><br />{$row['text']}</td>";
        if($need == "<en>Y</en><fr>O</fr>"){
            $html .= "<td align='center' style='font-size: 0.9em;'>{$education}</td>
                    <td align='center' style='font-size: 0.8em;'>{$programs}</td>
                    <td style='font-size: 0.9em;'>{$community}</td>";
        }
        else{
            $html .= "<td colspan='3'>{$row['no']}</td>";
        }
        $html .= "</tr>";
        return $html;
    }
    
    function generateReport($person){
        global $wgServer, $wgScriptPath, $config, $wgLang;
        $dir = dirname(__FILE__) . '/';
        require_once($dir . '/../../../../../Classes/SmartDomDocument/SmartDomDocument.php');
        $api = new UserFrailtyIndexAPI();
        $scores = $api->getFrailtyScore($person->getId());

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
                            }";
                            
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
                                    <th class='dark-top' colspan='5'>
                                        <en>The following risks and recommendations are from the health outcomes section of the assessment</en>
                                        <fr style='font-size:0.8em;'>Les recommandations et les risques suivants sont issus de la section de l’évaluation consacrée aux résultats en matière de santé.</fr>
                                    </th>
                                </tr>
                                <tr>
                                    <th rowspan='2' style='min-width: 6em; width: 6em; padding-bottom: 0; position: relative;'>
                                        <div style='line-height: 1em; position: absolute; top: 8px; left: 0;width:100%; text-align: center;'>
                                            <en>Risk<br />Identified?<br />(Y/N)</en>
                                            <fr>Risque<br />identifié?<br />(O/N)</fr>
                                        </div>
                                    </th>
                                    <th rowspan='2' style='min-width: 6em; width: 6em;'>
                                        <en>Topic</en>
                                        <fr>Sujet</fr>
                                    </th>
                                    <th class='dark' colspan='3'>
                                        <en>AVOID Frailty Program Support Recommendation</en>
                                        <fr>Recommandation de soutien du programme Proactif</fr>
                                    </th>
                                </tr>
                                <tr>
                                    <th align='left' style='width: 6.5em;'>
                                        <en><small><i>AVOID Frailty<br />Education<br />Topic<br /></i></small></en>
                                        <fr><small><i>Sujet d’éducation<br />du programme<br />Proacif</i></small></fr>
                                    </th>
                                    <th align='left' style='width: 9em;'>
                                        <en><small><i>AVOID Frailty<br />Programs<br /></i></small></en>
                                        <fr><small><i>Programmes offerts<br />dans le cadre<br />de Proactif</i></small></fr>
                                    </th>
                                    <th align='left' style='width: 13em;'>
                                        <en><small><i>Community Program<br />Category (Find these in the <br />Community Program Library)<br /></i></small></en>
                                        <fr><small><i>Catégorie de ressources<br />communautaires (dans le Répertoire<br />des ressources communautaires)</i></small></fr>
                                    </th>
                                </tr>
                            </table>
                        </div>
                        <div class='body'>
                        <img src='{$wgServer}{$wgScriptPath}/skins/bg_top.png' style='z-index: -2; position: absolute; top:0; left: 0; right:0; width: 216mm;' />
                        <div class='logos'>
                            <img src='{$wgServer}{$wgScriptPath}/skins/logo3.png' />
                            <img style='max-height: 100px;' src='{$wgServer}{$wgScriptPath}/skins/logo2.png' />
                            <img src='{$wgServer}{$wgScriptPath}/skins/logo1.png' />
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
                            <div class='pdfnodisplay' style='margin-top:1em;'>Your recommendations with direct links to resources are below.<br />You can also print your personal report <a href='{$wgServer}{$wgScriptPath}/index.php/Special:FrailtyReport' target='_blank'><b><u>here</u></b></a>.</div>
                        </div>
                        <div class='list'>
                            <p><img class='li' src='{$wgServer}{$wgScriptPath}/skins/li.png' /><en>This report shows the items that went into your frailty status. Where a need was identified from your answers, some recommended resources appear in that topic to address that specific item. If you do not see any recommendations, it means that no needs were identified from your answers.</en><fr>Ce rapport montre les domaines évalués pour mesurer votre état de fragilité. Lorsqu’un besoin est établi à partir de vos réponses, certaines ressources recommandées apparaissent dans cette rubrique concernant ce domaine précis. Si vous ne voyez pas de recommandations, cela signifie qu’aucun besoin n’a été établi à partir de vos réponses.</fr>
                            </p>
                            <p><img class='li' src='{$wgServer}{$wgScriptPath}/skins/star.png' /><en>The recommendations throughout this program are meant to support healthy 
behaviour. They are not clinical recommendations, for which you should seek advice from your health care providers (example: doctor, pharmacist, dentist).</en><fr>Les recommandations formulées tout au long de ce programme visent à favoriser un vieillissement en santé. Il ne s’agit pas de recommandations cliniques pour lesquelles vous devez demander conseil à un professionnel de la santé (p. ex. médecin, pharmacien, dentiste).</fr></p>
                        </div>
                        <br />
                        <br />
                        
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
                                <th class='dark-top' colspan='5'>
                                    <en>The following risks and recommendations are from the health outcomes section of the assessment</en>
                                    <fr style='font-size:0.8em;'>Les recommandations et les risques suivants sont issus de la section de l’évaluation consacrée aux résultats en matière de santé.</fr>
                                </th>
                            </tr>
                            <tr>
                                <th rowspan='2' style='min-width: 6em; width: 6em; padding-bottom: 0; position: relative;'>
                                    <div style='line-height: 1em; position: absolute; top: 8px; left: 0;width:100%; text-align: center;'>Risk<br />Identified?<br />(Y/N)</div>
                                </th>
                                <th rowspan='2' style='min-width: 6em; width: 6em;'>
                                    <en>Topic</en>
                                    <fr>Sujet</fr>
                                </th>
                                <th class='dark' colspan='3'>
                                    AVOID Frailty Program Support Recommendation
                                </th>
                            </tr>
                            <tr>
                                <th align='left' style='width: 6.5em;'>
                                    <en><small><i>AVOID Frailty<br />Education<br />Topic<br /></i></small></en>
                                    <fr><small><i>Sujet d’éducation<br />du programme<br />Proacif</i></small></fr>
                                </th>
                                <th align='left' style='width: 9em;'>
                                    <en><small><i>AVOID Frailty<br />Programs<br /></i></small></en>
                                    <fr><small><i>Programmes offerts<br />dans le cadre<br />de Proactif</i></small></fr>
                                </th>
                                <th align='left' style='width: 13em;'>
                                    <en><small><i>Community Program<br />Category (Find these in the <br />Community Program Library)<br /></i></small></en>
                                    <fr><small><i>Catégorie de ressources<br />communautaires (dans le Répertoire<br />des ressources communautaires)</i></small></fr>
                                </th>
                            </tr>";
        foreach(self::$healthRows as $key => $row){
            $html .= $this->drawRow($key, $row, $scores);
        }
        
        $html .= "<tr><td class='white' colspan='5'></td></tr>
                  <tr style='page-break-after: avoid;'>
                    <th class='dark-top' colspan='5'>
                        <en>The following risks and recommendations are from the behavioural portion of the assessment</en>
                        <fr style='font-size:0.8em;'>Les recommandations et les risques suivants sont issus de la section de l’évaluation consacrée aux résultats en matière de santé.</fr>
                    </th></tr>";
        foreach(self::$behavioralRows as $key => $row){
            $html .= $this->drawRow($key, $row, $scores["Behavioral"]);
        }
        
        $actionPlan = ActionPlan::newFromUserId($person->getId());
        $actionPlanMessage = "";
        if(!isset($actionPlan[0]) || $actionPlan[0]->getSubmitted()){
            $actionPlanMessage = "Are you ready to create a goal to improve your health?  <a href='#' onClick='parent.clickActionPlan();'>Create Action Plan Now</a> (closes Frailty Report)";
        }
        else{
            $actionPlanMessage = "Are you ready to create a goal to improve your health?  <a href='#' onClick='parent.clickActionPlan();'>View Action Plan Now</a> (closes Frailty Report)";
        }
        
        $html .= "      </table>
                        <br />
                        <p><img class='li' src='{$wgServer}{$wgScriptPath}/skins/li.png' /><en>While the behaviours recommended for all AVOID components play a role in a healthy lifestyle, you may want to focus on one or a few at a time - this report can help you decide where to start and focus your efforts. You can use this as a place to start on your healthy aging journey to help you develop an action plan around one or more of the topics that can best help slow the onset of frailty for you personally.</en><fr>Bien que les comportements recommandés pour toutes les composantes du programme Proactif pour éviter la fragilisation jouent un rôle dans un mode de vie sain, vous voudrez peut-être vous concentrer sur un ou plusieurs d’entre eux à la fois; ce rapport peut vous aider à décider par où commencer et sur quoi concentrer vos efforts. Vous pouvez l’utiliser comme point de départ de votre parcours de vieillissement en santé. Il vous sera ainsi plus facile d’élaborer un plan d’action autour d’un ou de plusieurs des domaines qui contribueront de manière optimale à ralentir l’apparition de la fragilité pour vous, personnellement.</fr>
                        <br />
                        <br />
                        {$actionPlanMessage}
                        </p>
                        <div style='width:100%; text-align:center;'><a href='https://HealthyAgingCentres.ca' target='_blank'>HealthyAgingCentres.ca</a></div>
                        <br /><br /><br /><br /><br />
                        <img src='{$wgServer}{$wgScriptPath}/skins/bg_bottom.png' style='z-index: -2; position: absolute; bottom:0; left: 0; right:0; width: 216mm;' />
                        <script type='text/javascript'>
                            var initialWidth = $(window).width();
                            var wgLang = '{$wgLang->getCode()}';
                            $(window).resize(function(){
                                $('html').width('100%');
                                var desiredWidth = $(window).width();
                                $('html').width('216mm');
                                var scaleFactor = desiredWidth/initialWidth;
                                $('div.body').css('transform', 'scale(' + scaleFactor + ')');
                                $('div.stickyContainer').css('top', 496*scaleFactor);
                                if(wgLang == 'fr'){
                                    $('div.stickyContainer').css('top', parseFloat($('div.stickyContainer').css('top')) + 16*scaleFactor);
                                }
                                $('table.sticky').css('transform', 'scale(' + scaleFactor + ')')
                                                 .css('margin-left', scaleFactor - 1 + 'cm');
                                $('body').height($('div.body').outerHeight()*scaleFactor);
                            }).resize();
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
