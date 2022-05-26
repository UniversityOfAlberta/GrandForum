<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['IntakeSummary'] = 'IntakeSummary'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['IntakeSummary'] = $dir . 'IntakeSummary.i18n.php';
$wgSpecialPageGroups['IntakeSummary'] = 'reporting-tools';

$wgHooks['SubLevelTabs'][] = 'IntakeSummary::createSubTabs';

function runIntakeSummary($par) {
    IntakeSummary::execute($par);
}

class IntakeSummary extends SpecialPage {
    
    static $map = array(
        'avoid_age' => 'Age',
        'avoid_gender' => 'Gender',
        'GENDERSPECIFY' => 'Gender (Specify)',
        'POSTAL' => 'Postal Code',
        'ethnicity_avoid' => 'Ethnicity1',
        'ethnicity_avoid2' => 'Ethnicity2',
        'ETHNICITYINDIGENOUSSPECIFY' => 'Ethnicity (Specify)',
        'transportation_avoid' => 'Transportation',
        'income_avoid' => 'Income',
        'assistive_avoid' => 'Assistive',
        'living_avoid' => 'Living',
        'living_environment' => 'Living Environment',
        'LIVINGSPECIFY' => 'Living (Specify)',
        'education_avoid' => 'Education',
        'employment_avoid' => 'Employment',
        'EMPLOYSPECIFY' => 'Employ (Specify)',
        'device_avoid' => 'Device',
        'DEVICESPECIFY' => 'Device (Specify)',
        'tech_avoid' => 'Tech',
        'internetissues_avoid' => 'Internet Issues',
        'program_avoid' => 'AVOID Program',
        'PROGRAMLOCATIONSPECIFY' => 'AVOID Program (Specify)',
        'platform_avoid' => 'Platform',
        'PROGRAMPLATFORMOTHERSPECIFY' => 'Platform (Specify)',
        'PROGRAMOTHERSPECIFY' => 'Program (Specify)',
        'behave1_avoid' => 'Activity1',
        'behave0_avoid' => 'Activity2',
        'behave2_avoid' => 'Activity3',
        'active_specify_end' => 'Activity4',
        'ACTIVESPECIFY' => 'Activity4 (Specify)',
        'vaccinate1_avoid' => 'Vaccinate1',
        'VACCINATESPECIFY' => 'Vaccinate1 (Specify)',
        'vaccinate2_avoid' => 'Vaccinate2',
        'vaccinate3_avoid' => 'Vaccinate3',
        'vaccinate3specify_avoid' => 'Vaccinate3 (Specify)',
        'vaccinate4_avoid' => 'Vaccinate4',
        'vaccinate5_avoid' => 'Vaccinate5',
        'vaccinate6_avoid' => 'Vaccinate6',
        'vaccinate7_avoid' => 'Vaccinate7',
        'vax_end' => 'Vaccinate8',
        'VAXENDTEXTSPECIFY' => 'Vaccinate8 (Specify)',
        'meds1_avoid' => 'Medication1',
        'meds2_avoid' => 'Medication2',
        'meds3_avoid' => 'Medication3',
        'meds_end' => 'Medication4',
        'MEDSENDTEXTSPECIFY' => 'Medication4 (Specify)',
        'interact1_avoid' => 'Interact1',
        'interact2_avoid' => 'Interact2',
        'interact3_avoid' => 'Interact3',
        'interact4_avoid' => 'Interact4',
        'interact5_avoid' => 'Interact5',
        'interact6_avoid' => 'Interact6',
        'interact7_avoid' => 'Interact7',
        'interact8_avoid' => 'Interact8',
        'interact9_avoid' => 'Interact9',
        'interact_end' => 'Interact10',
        'INTERACTENDTEXTSPECIFY' => 'Interact10 (Specify)',
        'diet1_avoid' => 'Diet1',
        'diet2_avoid' => 'Diet2',
        'diet3_avoid' => 'Diet3',
        'diet4_avoid' => 'Diet4',
        'diet_end' => 'Diet5',
        'DIETENDTEXTSPECIFY' => 'Diet5 (Specify)',
        'lifestyle' => 'Readiness (Activity)',
        'lifestyle2' => 'Readiness (Vaccination)',
        'lifestyle3' => 'Readiness (Medication)',
        'lifestyle4' => 'Readiness (Social Interaction)',
        'lifestyle5' => 'Readiness (Diet)',
        'healthstatus_avoid' => 'Mobility',
        'healthstatus_avoid2' => 'Self-Care',
        'healthstatus_avoid3' => 'Usual Activity',
        'healthstatus_avoid4' => 'Pain / Discomfort',
        'healthstatus_avoid5' => 'Anxiety / Depression',
        'healthstatus_avoid6' => 'Self-Assessment',
        'symptoms_avoid1' => 'Health Status1',
        'symptoms_avoid2' => 'Health Status2',
        'symptoms_avoid3' => 'Health Status3',
        'symptoms_avoid4' => 'Health Status4',
        'symptoms_avoid5' => 'Health Status5',
        'symptoms_avoid21' => 'Health Status6',
        'symptoms_avoid6' => 'Health Status7',
        'symptoms_avoid7' => 'Health Status8',
        'symptoms_avoid8' => 'Health Status9',
        'SYMPTOMS8SPECIFY' => 'Health Status9 (Specify)',
        'symptoms_avoid9' => 'Health Status10',
        'symptoms_avoid10' => 'Health Status11',
        'symptoms_avoid11' => 'Health Status12',
        'symptoms_avoid12' => 'Health Status13',
        'symptoms_avoid13' => 'Health Status14',
        'symptoms_avoid14' => 'Health Status15',
        'symptoms_avoid15' => 'Health Status16',
        'symptoms_avoid16' => 'Health Status17',
        'symptoms_avoid17' => 'Health Status18',
        'symptoms_avoid18' => 'Health Status19',
        'symptoms_avoid' => 'Health Status20',
        'SYMPTOMSSPECIFY' => 'Health Status20 (Specify)',
        'symptoms_avoid19' => 'Health Status21',
        'symptoms_avoid20' => 'Health Status22',
        'help_avoid' => 'Health Status23',
        'help2_avoid' => 'Health Status24',
        'help3_avoid' => 'Health Status25',
        'help4_avoid' => 'Health Status26',
        'help5_avoid' => 'Health Status27',
        'help6_avoid' => 'Health Status28',
        'help7_avoid' => 'Health Status29',
        'help8_avoid' => 'Health Status30',
        'help9_avoid' => 'Health Status31',
        'help10_avoid' => 'Health Status32',
        'help11_avoid' => 'Health Status33',
        'help12_avoid' => 'Health Status34',
        'help13_avoid' => 'Health Status35'
    );
    
    function __construct() {
        SpecialPage::__construct("IntakeSummary", null, true, 'runIntakeSummary');
    }
    
    function userCanExecute($wgUser){
        $person = Person::newFromUser($wgUser);
        return $person->isRoleAtLeast(STAFF);
    }
    
    function execute($par){
        global $wgServer, $wgScriptPath, $wgOut;
        $me = Person::newFromWgUser();
        $wgOut->setPageTitle("Intake Summary");
        $people = Person::getAllPeople(CI);
        
        $report = new DummyReport("IntakeSurvey", $me, null, YEAR);
        
        $wgOut->addHTML("<table id='summary' class='wikitable'>
                            <thead>
                            <tr>
                                <th>Frailty Score</th>");
        foreach($report->sections as $section){
            foreach($section->items as $item){
                if($item->blobItem != "" && $item->blobItem !== 0){
                    $label = (isset(self::$map[$item->blobItem])) ? self::$map[$item->blobItem] : str_replace("_", " ", $item->blobItem);
                    $wgOut->addHTML("<th>{$label}</th>");
                }
            }
        }                       
        $wgOut->addHTML("       </tr>
                            </thead>
                            <tbody>");
        
        foreach($people as $person){
            if(AVOIDDashboard::hasSubmittedSurvey($person->getId()) && $this->getBlobData("AVOID_Questions_tab0", "POSTAL", $person, YEAR) != "CFN"){
                $report->person = $person;
                $api = new UserFrailtyIndexAPI();
                $scores = $api->getFrailtyScore($person->getId());
                $wgOut->addHTML("<tr>
                                    <td>{$scores["Total"]}</td>");
                foreach($report->sections as $section){
                    foreach($section->items as $item){
                        if($item->blobItem != "" && $item->blobItem !== 0){
                            $value = $item->getBlobValue();
                            if(is_array($value)){
                                $wgOut->addHTML("<td>".implode(", ", $value)."</td>");
                            }
                            else{
                                $wgOut->addHTML("<td>{$value}</td>");
                            }
                        }
                    }
                }
                $wgOut->addHTML("</tr>");
            }
        }
        $wgOut->addHTML("</tbody>
                        </table>
        <script type='text/javascript'>
            $('#summary').DataTable({
                'aLengthMenu': [[10, 25, 100, 250, -1], [10, 25, 100, 250, 'All']],
                'iDisplayLength': -1,
                'dom': 'Blfrtip',
                'buttons': [
                    'excel'
                ]
            });
        </script>");
    }
    
    static function createSubTabs(&$tabs){
        global $wgUser, $wgServer, $wgScriptPath, $wgTitle;
        $person = Person::newFromWgUser();
        if($person->isRoleAtLeast(STAFF)){
            $selected = @($wgTitle->getText() == "IntakeSummary") ? "selected" : false;
            $tabs['Manager']['subtabs'][] = TabUtils::createSubTab("Intake Summary", "{$wgServer}{$wgScriptPath}/index.php/Special:IntakeSummary", $selected);
        }
        return true;
    }
    
    function getBlobData($blobSection, $blobItem, $person, $year){
        $blb = new ReportBlob(BLOB_TEXT, $year, $person->getId(), 0);
        $addr = ReportBlob::create_address("RP_AVOID", $blobSection, $blobItem, 0);
        $result = $blb->load($addr);
        return $blb->getData();
    }
    
}

?>
