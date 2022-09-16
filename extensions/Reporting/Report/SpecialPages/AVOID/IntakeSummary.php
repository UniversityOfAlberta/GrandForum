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
    
    static $pageTitle = "Intake Summary";
    static $reportName = "IntakeSurvey";
    static $rpType = "RP_AVOID";
    
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
        'behave1_avoid' => 'Time spent sitting',
        'behave0_avoid' => 'Walked >10 min',
        'behave2_avoid' => 'Mod phys activity',
        'active_specify_end' => 'Challenges active',
        'ACTIVESPECIFY' => 'Specify',
        'vaccinate1_avoid' => "Can't be vaccinated?",
        'VACCINATESPECIFY' => 'Reason',
        'vaccinate2_avoid' => 'Flu vaccine',
        'vaccinate3_avoid' => 'Shingles vaccine',
        'vaccinate3specify_avoid' => 'Which type?',
        'vaccinate4_avoid' => 'Pneumonia vaccine',
        'vaccinate5_avoid' => 'Booster vaccines',
        'vaccinate6_avoid' => '2 covid vaccines',
        'vaccinate7_avoid' => 'covid booster',
        'vax_end' => 'Vaccine Barriers',
        'VAXENDTEXTSPECIFY' => 'Other Barrier',
        'meds1_avoid' => '# Rx Meds',
        'meds2_avoid' => '# OTC meds',
        'meds3_avoid' => 'Had med review?',
        'meds_end' => 'Med Barriers',
        'MEDSENDTEXTSPECIFY' => 'Other Barrier',
        'interact1_avoid' => '# relatives see/hear',
        'interact2_avoid' => '# relatives trust',
        'interact3_avoid' => '# relatives can help',
        'interact4_avoid' => '# friends see/hear',
        'interact5_avoid' => '# friends trust',
        'interact6_avoid' => '# friends can help',
        'interact7_avoid' => 'Lack companionship?',
        'interact8_avoid' => 'Feel left out?',
        'interact9_avoid' => 'Feel isolated?',
        'interact_end' => 'Interat Barriers',
        'INTERACTENDTEXTSPECIFY' => 'Other Barrier',
        'diet1_avoid' => 'Protein w meals?',
        'diet2_avoid' => '1/2 plate F&V',
        'diet3_avoid' => 'Calcium daily?',
        'diet4_avoid' => 'Vit D Supp',
        'diet_end' => 'Diet Barriers',
        'DIETENDTEXTSPECIFY' => 'Other Barrier',
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
        'symptoms_avoid1' => 'Felt everything effort?',
        'symptoms_avoid2' => 'Tired/Fatigued?',
        'symptoms_avoid3' => 'Bothered by worrying?',
        'symptoms_avoid4' => 'BB no interest?',
        'symptoms_avoid5' => 'Memory Overall?',
        'symptoms_avoid21' => 'Balance Trouble?',
        'symptoms_avoid6' => '# falls last year',
        'symptoms_avoid7' => 'Walk Speed',
        'symptoms_avoid8' => 'Walk distance limited',
        'SYMPTOMS8SPECIFY' => 'Distance',
        'symptoms_avoid9' => 'Sleep quality',
        'symptoms_avoid10' => 'Food intake decrease',
        'symptoms_avoid11' => 'Lost > 3 kg in 3 mos',
        'symptoms_avoid12' => 'Pain while chewing',
        'symptoms_avoid13' => 'Mouth problems',
        'symptoms_avoid14' => 'Bodily pain',
        'symptoms_avoid15' => 'Pain in feet',
        'symptoms_avoid16' => 'Weak hands',
        'symptoms_avoid17' => 'Weak legs/feet',
        'symptoms_avoid18' => 'Leaked urine',
        'symptoms_avoid' => 'Health cond',
        'SYMPTOMSSPECIFY' => 'Other health cond',
        'symptoms_avoid19' => 'Eyesight problems',
        'symptoms_avoid20' => 'Hearing problems',
        'help_avoid' => 'Help eating?',
        'help2_avoid' => 'Help dressing?',
        'help3_avoid' => 'Help transfer?',
        'help4_avoid' => 'Help toileting?',
        'help5_avoid' => 'Help bathing?',
        'help6_avoid' => 'Help shopping',
        'help7_avoid' => 'Help meds?',
        'help8_avoid' => 'Help phone',
        'help9_avoid' => 'Help financing?',
        'help10_avoid' => 'Help transport?',
        'help11_avoid' => 'Help meal prep?',
        'help12_avoid' => 'Help housework?',
        'help13_avoid' => 'Help laundry?',
        'evaluation1' => 'In person opportunity1',
        'evaluation2' => 'In person opportunity2'
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
        $wgOut->setPageTitle(static::$pageTitle);
        $people = Person::getAllPeople(CI);
        
        $report = new DummyReport(static::$reportName, $me, null, YEAR);
        
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
                                    <td>".number_format($scores["Total"]/36, 3)."</td>");
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
                'scrollX': true,
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
        $addr = ReportBlob::create_address(static::$rpType, $blobSection, $blobItem, 0);
        $result = $blb->load($addr);
        return $blb->getData();
    }
    
}

?>
