<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['IndexComponents'] = 'IndexComponents'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['IndexComponents'] = $dir . 'IndexComponents.i18n.php';
$wgSpecialPageGroups['IndexComponents'] = 'reporting-tools';

$wgHooks['SubLevelTabs'][] = 'IndexComponents::createSubTabs';

function runIndexComponents($par) {
    IndexComponents::execute($par);
}

class IndexComponents extends SpecialPage {
    
    function __construct() {
        global $config;
        SpecialPage::__construct("IndexComponents", null, true, 'runIndexComponents');
    }
    
    function userCanExecute($wgUser){
        $person = Person::newFromUser($wgUser);
        return $person->isRoleAtLeast(STAFF);
    }
    
    static function getRow($person){
        global $wgServer, $wgScriptPath, $config, $EQ5D5L;
        $me = Person::newFromWgUser();

        $api1 = new UserFrailtyIndexAPI();
        $api2 = new UserInPersonFrailtyIndexAPI();
        $scores1 = $api1->getFrailtyScore($person->getId(), "RP_AVOID");
        $scores2 = $api2->getFrailtyScore($person->getId());
        $html = "";
        $html .= "<tr data-id='{$person->getId()}'>
                    <td>{$person->getId()}</td>
                    <td>{$scores1['Physical Activity']}</td>
                    <td>{$scores1['Multiple Medications']}</td>
                    <td>{$scores1['Fatigue']}</td>
                    <td>{$scores1['Mental Health']}</td>
                    <td>{$scores1['Memory']}</td>
                    <td>{$scores1['Falls and Balance']}</td>
                    <td>{$scores1['Walking Speed']}</td>
                    <td>{$scores1['Nutritional Status']}</td>
                    <td>{$scores1['Oral Health']}</td>
                    <td>{$scores1['Pain']}</td>
                    <td>{$scores1['Strength']}</td>
                    <td>{$scores1['Urinary Continence']}</td>
                    <td>{$scores1['Sensory: Hearing and Vision']}</td>
                    <td>{$scores1['Self-Perceived Health']}</td>
                    <td>{$scores1['Health Conditions']}</td>
                 
                    <td>{$scores2['Vision']}</td>
                    <td>{$scores2['Hearing']}</td>
                    <td>{$scores2['Communication']}</td>
                    <td>{$scores2['Cognition']}</td>
                    <td>{$scores2['Dementia']}</td>
                    <td>{$scores2['Depression']}</td>
                    <td>{$scores2['Balance']}</td>
                    <td>{$scores2['ADL']}</td>
                    <td>{$scores2['IADL']}</td>
                    <td>{$scores2['Caregiver']}</td>
                    <td>{$scores2['Urinary']}</td>
                    <td>{$scores2['Bowel']}</td>
                    <td>{$scores2['Medications']}</td>
                    <td>{$scores2['Fatigue']}</td>
                    <td>{$scores2['Strength']}</td>
                    <td>{$scores2['Nutrition']}</td>
                    <td>{$scores2['Osteoporosis']}</td>
                    <td>{$scores2['Pain']}</td>
                    <td>{$scores2['Dental']}</td>
                    <td>{$scores2['Lifestyle']}</td>
                    <td>{$scores2['Chronic']}</td>
                 </tr>";

        return $html;
    }

    function execute($par){
        global $wgServer, $wgScriptPath, $wgOut;
        $me = Person::newFromWgUser();
        $people = Person::getAllPeople(CI);
        
        $wgOut->addHTML("<table id='summary' class='wikitable'>
                            <thead>
                                <tr>
                                    <th rowspan='2'>User Id</th>
                                    <th colspan='15'>Frailty Index</th>
                                    <th colspan='21'>In Person</th>
                                </tr>
                                <tr>
                                    <th>Physical Activity</th>
                                    <th>Multiple Medications</th>
                                    <th>Fatigue</th>
                                    <th>Mental Health</th>
                                    <th>Memory</th>
                                    <th>Falls and Balance</th>
                                    <th>Walking Speed</th>
                                    <th>Nutritional Status</th>
                                    <th>Oral Health</th>
                                    <th>Pain</th>
                                    <th>Strength</th>
                                    <th>Urinary Continence</th>
                                    <th>Sensory: Hearing and Vision</th>
                                    <th>VAS</th>
                                    <th>Health Conditions</th>
                                    
                                    <th>Vision</th>
                                    <th>Hearing</th>
                                    <th>Communication</th>
                                    <th>Cognition</th>
                                    <th>Dementia</th>
                                    <th>Depression</th>
                                    <th>Balance/Falls/Mobility</th>
                                    <th>ADL</th>
                                    <th>IADL</th>
                                    <th>Caregiver</th>
                                    <th>Urinary</th>
                                    <th>Bowel</th>
                                    <th>Medications</th>
                                    <th>Fatigue</th>
                                    <th>Strength</th>
                                    <th>Nutrition</th>
                                    <th>Osteoporosis</th>
                                    <th>Pain</th>
                                    <th>Dental</th>
                                    <th>Lifestyle</th>
                                    <th>Chronic</th>
                                </tr>
                            </thead>");
        $wgOut->addHTML("<tbody>");
        
        foreach($people as $person){
            if(!$person->isRoleAtMost(CI)){
                continue;
            }
            if(AVOIDDashboard::hasSubmittedSurvey($person->getId(), "RP_AVOID") && IntakeSummary::getBlobData("AVOID_Questions_tab0", "POSTAL", $person, YEAR, "RP_AVOID") != "CFN"){
                $wgOut->addHTML(self::getRow($person));
            }
        }
        $wgOut->addHTML("</tbody>
                        </table>");
        $wgOut->addHTML("
        <style>
            .downloadExcel {
                float: left;
            }
        </style>                
        <script type='text/javascript'>
            $('#summary').DataTable({
                'aLengthMenu': [[10, 25, 100, 250, -1], [10, 25, 100, 250, 'All']],
                'scrollX': true,
                'iDisplayLength': -1,
                'dom': 'Blfrtip',
                'buttons': [
                    'excel'
                ],
                scrollX: true,
                scrollY: $('#bodyContent').height() - 400
            });
        </script>");
    }
    
    static function createSubTabs(&$tabs){
        global $wgUser, $wgServer, $wgScriptPath, $wgTitle, $config;
        $person = Person::newFromWgUser();
        if($person->isRoleAtLeast(STAFF) && $config->getValue('networkFullName') != "AVOID Australia"){
            $selected = @($wgTitle->getText() == "IndexComponents") ? "selected" : false;
            $tabs['Manager']['subtabs'][] = TabUtils::createSubTab("Index Components", "{$wgServer}{$wgScriptPath}/index.php/Special:IndexComponents", $selected);
        }
        return true;
    }
    
}

?>
