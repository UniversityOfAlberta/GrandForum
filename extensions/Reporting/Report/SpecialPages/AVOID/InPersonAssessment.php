<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['InPersonAssessment'] = 'InPersonAssessment'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['InPersonAssessment'] = $dir . 'InPersonAssessment.i18n.php';
$wgSpecialPageGroups['InPersonAssessment'] = 'reporting-tools';

$wgHooks['TopLevelTabs'][] = 'InPersonAssessment::createTab';
$wgHooks['SubLevelTabs'][] = 'InPersonAssessment::createSubTabs';

function runInPersonAssessment($par) {
    InPersonAssessment::execute($par);
}

class InPersonAssessment extends SpecialPage {
    
    static $map = array(
        "avoid_vision" => "Snellen Eye",
        "avoid_vision_fup" => "Snellen fup",
        "avoid_vision_fup_txt" => "Snellen fup txt",
        "avoid_vision_fup_recommended" => "Snellen fup recommended",
        "avoid_hearing" => "Hearing Difficulty",
        "avoid_hearing_whisper1" => "Whisper Test 1",
        "avoid_hearing_whisper2" => "Whisper Test 2",
        "avoid_hearing_whisper3" => "Whisper Test 3",
        "avoid_hearing_fup" => "Hearing fup",
        "avoid_hearing_fup_txt" => "Hearing fup txt",
        "avoid_hearing_fup_recommended" => "Hearing fup recommended",
        "avoid_communication" => "Communication Difficulty",
        "avoid_communication2" => "Find Words Difficulty",
        "avoid_communication3" => "Recall Names Diff",
        "avoid_communication_fup" => "Communication fup",
        "avoid_communication_fup_txt" => "Communication fup txt",
        "avoid_cognition" => "Pair Assoc Score",
        "avoid_cognition2" => "Pair Assoc %",
        "avoid_cognition3" => "Pair Assoc Choose",
        "avoid_cognition2_1" => "Polygon Score",
        "avoid_cognition2_2" => "Polygon %",
        "avoid_cognition2_3" => "Polygon Choose",
        "avoid_cognition3_1" => "Feature Match %",
        "avoid_cognition3_2" => "Feature Match Choose",
        "avoid_cognition3_3" => "Feature Match Score",
        "avoid_cognition4_1" => "Double Trouble Score",
        "avoid_cognition4_2" => "Double Trouble %",
        "avoid_cognition4_3" => "Double Trouble choose",
        "avoid_cognition_comments" => "Cognition comments",
        "avoid_cognition5" => "Cognition Further Testing?",
        "avoid_cognition_fup_recommended" => "Cognition fup recommended",
        "avoid_dementia" => "Dementia word recall",
        "avoid_dementia2" => "Dementia animal name",
        "avoid_dementia3" => "Dementia clock drawing",
        "avoid_dementia3_fup" => "Dementia Fup? ",
        "avoid_dementia_fup_txt" => "Dementia fup txt",
        "avoid_depression" => "Dep Life satisfaction",
        "avoid_depression2" => "Dep bored?",
        "avoid_depression3" => "Dep feel helpless?",
        "avoid_depression4" => "Dep prefer stay home?",
        "avoid_depression5" => "Dep feel worthless?",
        "avoid_depression_fup" => "Dep fup",
        "avoid_depression_fup_txt" => "Dep fup txt",
        "avoid_depression_fup_recommended" => "Dep fup recommended",
        "avoid_balance3" => "fall in 12 months?",
        "avoid_balance4" => "medical atten?",
        "avoid_balance5" => "fall fear?",
        "avoid_balance_fup" => "fall fup",
        "avoid_balance6" => "TUG (seconds)",
        "avoid_balance8" => "TUG comments",
        "avoid_balance2" => "TUG unsteadiness concern",
        "avoid_balance12" => "TUG direction change concern",
        "avoid_balance9" => "4 metre walk (sec)",
        "avoid_balance11" => "4 metre walk comments",
        "avoid_balance_fup_recommended" => "balance recommendations",
        "avoid_adl" => "Adl difficulties",
        "avoid_adl2" => "Ald comments",
        "avoid_adl_fup" => "Adl fup",
        "avoid_adl_fup_recommended" => "Adl fup recommended",
        "avoid_iadl" => "Ladl difficulties",
        "avoid_iadl2" => "Ladl comments",
        "avoid_iadl_fup" => "Ladl fup",
        "avoid_iadl_fup_recommended" => "Ladl fup recommended",
        "avoid_caregiver" => "Receiving assistance?",
        "avoid_caregiver3" => "Caregiver frequency",
        "avoid_caregiver4" => "Caregiver comments",
        "avoid_caregivere_fup" => "Caregivere fup",
        "avoid_caregiver_fup_recommended" => "Caregiver fup recommended",
        "avoid_urinary" => "Urinary problems",
        "avoid_urinary2" => "Urinary followup?",
        "avoid_urinary_recommended" => "Urinary recommended",
        "avoid_bowel" => "Constipated?",
        "avoid_bowel2_2" => "Bowel comment",
        "avoid_bowel3" => "Bowel change?",
        "avoid_bowel4" => "Change to stool?",
        "avoid_bowel5" => "Change to stool how?",
        "avoid_bowel3_2" => "Bowel comments",
        "avoid_uravoid_bowel_fupinary_fup" => "Bowel fup?",
        "avoid_bowel_fup_recommended" => "Bowel fup recommended",
        "avoid_meds" => " RX meds",
        "avoid_meds2" => "OTC meds",
        "avoid_meds3" => "Med concerns",
        "avoid_meds4" => "Concern comments",
        "avoid_meds_fup" => "Meds fup",
        "avoid_meds_fup_recommended" => "Meds fup recommended",
        "avoid_meds5" => "Meds comments",
        "avoid_fatigue" => "Feeling fatigued?",
        "avoid_fatigue2" => "How many days/week?",
        "avoid_fatigue_fup" => "Fatigue fup",
        "avoid_fatigue_fup_recommended" => "Fatigue fup recommended",
        "avoid_strength" => "Dominant hand used",
        "avoid_strength2" => "Strength Test 1",
        "avoid_strength3" => "Strength Test 2",
        "avoid_strength4" => "Strength Test 3",
        "avoid_strength5" => "30 sec.chair stand reps",
        "avoid_strength_fup_recommended" => "Strength fup recommended",
        "avoid_nutrition1" => "weight",
        "avoid_nutrition1_2" => "lbs/kilos",
        "avoid_nutrition1_2_2" => "unplanned weight loss",
        "avoid_nutrition1_3" => "weight loss amount",
        "avoid_nutrition1_4" => "lbs/kilos loss",
        "avoid_nutrition2_1" => "height",
        "avoid_nutrition2_2" => "Feet/inches/cm",
        "avoid_nutrition2_2_2" => "Inches",
        "avoid_nutrition4" => "follow Canada's FG",
        "avoid_nutrition5" => "BMI",
        "avoid_nutrition_fup" => "Nutrition fup",
        "avoid_nutrition_fup_txt" => "Nutrition fup txt",
        "avoid_nutrition_fup_recommended" => "Nutrition fup recommended",
        "avoid_osteo" => "How much Vit D",
        "avoid_osteo1" => "IU/OD",
        "avoid_osteo2" => "Calcium servings/day",
        "avoid_osteo_fup" => "Osteo fup",
        "avoid_osteo_fup_recommended" => "Osteo fup recommended",
        "avoid_pain" => "Suffer from pain?",
        "avoid_pain2" => "Pain scale",
        "avoid_pain_fup" => "Pain fup",
        "avoid_pain_fup_txt" => "Pain fup txt",
        "avoid_pain_fup_recommended" => "Pain fup recommended",
        "avoid_immunization1_1" => "Flu shot",
        "avoid_immunization1_2" => "Flu shot date",
        "avoid_immunization2_1" => "Tetnus shot",
        "avoid_immunization2_2" => "Tetnus shot date",
        "avoid_immunization3_1" => "Pneumococcal",
        "avoid_immunization3_2" => "Pneumococcal date",
        "avoid_immunization4_1" => "Shingles",
        "avoid_immunization4_2" => "Shingles date",
        "avoid_immunization5_1" => "Covid",
        "avoid_immunization5_2" => "Covid date",
        "avoid_immunization_fup_recommended" => "Immunization fup recommended",
        "avoid_dental" => "Brush/floss regularly",
        "avoid_dental2" => "Yearly dental cleaning",
        "avoid_dental3" => "Gums bleed",
        "avoid_dental4" => "Dental counselling",
        "avoid_dental_fup" => "Dental fup",
        "avoid_dental_fup_txt" => "Dental fup txt",
        "avoid_dental_fup_recommended" => "Dental fup recommended",
        "avoid_lifestyle" => "Drink alcohol",
        "avoid_lifestyle2" => "Drinks/week",
        "avoid_lifestyle3" => "Smoke",
        "avoid_lifestyle4" => "Smokes/day",
        "avoid_lifestyle5" => "Smoking years",
        "avoid_lifestyle6" => "Thinking of quitting smoking",
        "avoid_lifestyle7" => "Aware of quitting resources",
        "avoid_lifestyle_fup" => "Lifestyle fup",
        "avoid_lifestyle_fup_txt" => "Lifestyle fup txt",
        "avoid_lifestyle_fup_recommended" => "Lifestyle fup recommended",
        "avoid_lifestyle_ex" => "Exercise regularly",
        "avoid_lifestyle8" => "How often/What type exercise",
        "avoid_lifestyle9" => "Exercise comments",
        "avoid_chronic" => "Chronic Diseases",
        "avoid_chronic_2" => "Chronic Other specified",
        "avoid_chronic_recommended" => "Chronic recommended",
        "DISPLAY" => "Y"
    );
    
    function __construct() {
        SpecialPage::__construct("InPersonAssessment", null, true, 'runInPersonAssessment');
    }
    
    function userCanExecute($wgUser){
        $person = Person::newFromUser($wgUser);
        return $person->isRole('Assessor');
    }
    
    function generateReport(){
        $api = new UserFrailtyIndexAPI();
        $scores = $api->getFrailtyScore($me->getId());
        exit;
    }
    
    function userTable(){
        global $wgOut;
        $me = Person::newFromWgUser();
        $report = new DummyReport(IntakeSummary::$reportName, $me, null, YEAR);
        
        $wgOut->addHTML("<table id='summary' class='wikitable' frame='box' rules='all'>
            <thead>
                <tr>
                    <th>Person</th>
                    ".IntakeSummary::getHeader($report, true, true)."
                </tr>
            </thead>
            <tbody>");
        
        $people = array();
        foreach(explode(",", $_GET['users']) as $id){
            $people[] = Person::newFromId($id);
        }
        
        foreach($people as $person){
            $report->person = $person;
            $report->reportType = "RP_AVOID";
            $wgOut->addHTML("<tr>
                <td>{$person->getNameForForms()}</td>
                ".IntakeSummary::getRow($person, $report, "Intake", true)."
            </tr>");
            
            $report->reportType = "RP_AVOID_THREEMO";
            $wgOut->addHTML("<tr>
                <td>{$person->getNameForForms()}</td>
                ".IntakeSummary::getRow($person, $report, "3 Month", true)."
            </tr>");
            
            $report->reportType = "RP_AVOID_SIXMO";
            $wgOut->addHTML("<tr>
                <td>{$person->getNameForForms()}</td>
                ".IntakeSummary::getRow($person, $report, "6 Month", true)."
            </tr>");
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
                ],
                scrollX: true,
                scrollY: $('#bodyContent').height() - 400
            });
        </script>");
    }
    
    static function getHeader($report){
        $html = "";
        foreach($report->sections as $section){
            foreach($section->items as $item){
                if($item->blobItem != "" && $item->blobItem !== 0 && isset(self::$map[$item->blobItem])){
                    $label = self::$map[$item->blobItem];
                    $html .= "<th>{$label}</th>";
                }
            }
        }
        return $html;
    }
    
    static function getRow($person, $report){
        $html = "";
        foreach($report->sections as $section){
            foreach($section->items as $item){
                if($item->blobItem != "" && $item->blobItem !== 0 && isset(self::$map[$item->blobItem])){
                    $value = $item->getBlobValue();
                    $labels = explode("|", $item->getAttr('labels', ''));
                    $options = explode("|", $item->getAttr('options', ''));
                    if(is_array($value)){
                        $html .= "<td>".implode(", ", $value)."</td>";
                    }
                    else{
                        $html .= "<td>{$value}</td>";
                    }
                }
            }
        }
        return $html;
    }
    
    function execute($par){
        global $wgOut, $wgServer, $wgScriptPath;
        if(isset($_GET['users'])){
            $this->userTable();
            return;
        }
        $me = Person::newFromWgUser();
        $report = new DummyReport(IntakeSummary::$reportName, $me, null, YEAR);
        $assessment = new DummyReport("RP_AVOID_INPERSON", $me, null, YEAR);
        $wgOut->setPageTitle("Assessor");
        $wgOut->addHTML("<table id='summary' class='wikitable' frame='box' rules='all'>
            <thead>
                <tr>
                    <th>Person</th>
                    <th>Frailty Report</th>
                    <th>In Person Assessment</th>
                    ".IntakeSummary::getHeader($report, false, true)."
                    ".InPersonAssessment::getHeader($assessment)."
                </tr>
            </thead>
            <tbody>");
        $rels = $me->getRelations("Assesses");
        foreach($rels as $rel){
            $person = $rel->getUser2();
            $report->person = $person;
            $assessment->person = $person;
            $wgOut->addHTML("<tr>
                <td><a href='{$wgServer}{$wgScriptPath}/index.php/Special:InPersonAssessment?users={$person->getId()}'>{$person->getNameForForms()}</a></td>
                <td><a href='{$wgServer}{$wgScriptPath}/index.php/Special:FrailtyReport?user={$person->getId()}' target='_blank'>Download</a>
                <td><a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=InPersonAssessment&person={$person->getId()}'>Form</a></td>
                ".IntakeSummary::getRow($person, $report, false, true)."
                ".InPersonAssessment::getRow($person, $assessment)."
            </tr>");
        }
        $wgOut->addHTML("</tbody></table>
        <script type='text/javascript'>
            $('#summary').DataTable({
                'aLengthMenu': [[10, 25, 100, 250, -1], [10, 25, 100, 250, 'All']],
                'iDisplayLength': -1,
                scrollX: true,
                'dom': 'Blfrtip',
                'buttons': [
                    'excel'
                ],
            });
        </script>");
    }

    static function createTab(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle;
        $me = Person::newFromWgUser();
        if($me->isRole("Assessor")){
            $selected = @($wgTitle->getText() == "InPersonAssessment" || ($wgTitle->getText() == "Report" && @$_GET['report'] == "InPersonAssessment")) ? "selected" : false;
            $GLOBALS['tabs']['InPersonAssessment'] = TabUtils::createTab("<en>Assessor</en><fr>Conseiller</fr>", "{$wgServer}{$wgScriptPath}/index.php/Special:InPersonAssessment", $selected);
        }
        return true;
    }


    static function createSubTabs(&$tabs){
        global $wgUser, $wgServer, $wgScriptPath, $wgTitle;
            $me = Person::newFromWgUser();
            if($me->isRole("Assessor")){
                $selected = @($wgTitle->getText() == "InPersonAssessment") ? "selected" : false;
            $tabs['InPersonAssessment']['subtabs'][] = TabUtils::createSubTab("Assessment", "{$wgServer}{$wgScriptPath}/index.php/Special:InPersonAssessment", $selected);
        }
        return true;
    }
    
}

?>
