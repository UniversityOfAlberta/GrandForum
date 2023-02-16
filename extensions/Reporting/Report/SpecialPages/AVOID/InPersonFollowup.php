<?php
    $dir = dirname(__FILE__) . '/';
    $wgSpecialPages['InPersonFollowup'] = 'InPersonFollowup'; # Let MediaWiki know about the special page.
    $wgExtensionMessagesFiles['InPersonFollowup'] = $dir . 'InPersonFollowup.i18n.php';
    $wgSpecialPageGroups['InPersonFollowup'] = 'reporting-tools';

    $wgHooks['SubLevelTabs'][] = 'InPersonFollowup::createSubTabs';

    function runInPersonFollowup($par){
        InPersonFollowup::execute($par);
    }

    class InPersonFollowup extends SpecialPage{
        static $pageTitle = "In-Person Assessment";
        static $reportName = "InPersonAssessment";
        static $rpType = "RP_AVOID_INPERSON";

        static $map = array(
            'avoid_vision' => 'Snellen Eye Chart Result:',
            'avoid_vision_fup_txt' => 'Follow up Comments:',

            'avoid_hearing' => 'Difficulties in hearing detected?',
            'avoid_hearing_whisper1' => 'Whisper Test 1 Result:',
            'avoid_hearing_whisper2' => 'Whisper Test 2 Result:',
            'avoid_hearing_whisper3' => 'Whisper Test 3 Result:',
            'avoid_hearing_fup_txt' => 'Follow up Comments',

            'avoid_communication' => 'Patient has trouble communicating wishes to people:',
            'avoid_communication2' => 'Patient has trouble finding words:',
            'avoid_communication3' => 'Patient has trouble recalling names:',
            'avoid_communication_fup_txt' => 'Follow up Comments',

            'avoid_cognition' => 'Paired Association Score:',
            'avoid_cognition2' => 'Paired Association Percentage:',
            'avoid_cognition3' => 'Paired Association Ranking:',
            'avoid_cognition2_1' => 'Polygons Score:',
            'avoid_cognition2_2' => 'Polygons Percentage:',
            'avoid_cognition2_3' => 'Polygons Ranking:',
            'avoid_cognition3_1' => 'Feature Match Score:',
            'avoid_cognition3_2' => 'Feature Match Percentage:',
            'avoid_cognition3_3' => 'Feature Match Ranking:',
            'avoid_cognition4_1' => 'Double trouble Score:',
            'avoid_cognition4_2' => 'Double trouble Percentage:',
            'avoid_cognition4_3' => 'Double trouble Ranking:',
            'avoid_cognition_comments' => 'Comments:',
            'avoid_cognition5' => 'Further Test needed?',

            'avoid_dementia' => 'Word Recall Result:',
            'avoid_dementia2' => 'Animal Naming Result:',
            'avoid_dementia3' => 'Clock Drawing Result:',
            'avoid_dementia_fup_txt' => 'Follow up Comments',

            'avoid_depression' => 'Is patient basically satisfied with your life?',
            'avoid_depression2' => 'Does patient often get bored?',
            'avoid_depression3' => 'Does patient often feel helpless?',
            'avoid_depression4' => 'Does patient prefer to stay at home rather than going out and doing new things?',
            'avoid_depression5' => 'Does patient feel pretty worthless the way they are now?',
            'avoid_depression_fup_txt' => 'Follow up Comments',

            'avoid_balance3' => 'Fall within the last 12 months:',
            'avoid_balance4' => 'Sought medical attention after a fall:',
            'avoid_balance5' => 'Fear of falling?',
            'avoid_balance_fup_txt' => 'Follow up Comments',
            'avoid_balance6' => 'Timed Get up and Go Test Results:',
            'avoid_balance7' => '',
            'avoid_balance8' => 'Timed Get up and Go Test Comments:',
            'avoid_balance2' => 'Unsteadiness during standing (during test):',
            'avoid_balance12' => 'Unsteadiness during changes in direction if walking (during test):',
            'avoid_balance2_bpgait' => 'BP and observe gait during TUG',
            'avoid_balance9' => '4 Metre Walk Test Results:',
            'avoid_balance10' => '',
            'avoid_balance11' => '4 Metre Walk Test Comments',


            'avoid_adl' => 'Difficulties or need reminding about everyday activities:',
            'avoid_adl2' => 'Comments:',
            'avoid_adl_fup_txt' => 'Follow up Comments:',

            'avoid_iadl' => 'Difficulties or need reminding about everyday activities:',
            'avoid_iadl2' => 'Comments:',
            'avoid_iadl_fup_txt' => 'Follow up Comments',

            'avoid_caregiver' => 'Patient receives assistance with everyday activities:',
            'avoid_caregiver2' => 'From whom:',
            'avoid_caregiver3' => 'Frequency:',
            'avoid_caregiver4' => 'Comments:',
            'avoid_caregiver_fup_txt' => 'Follow up Comments:',


            'avoid_urinary' => 'Problems with involuntary loss of water/urine:',
            'avoid_urinary2' => 'If yes, need for further assessment:',
            'avoid_urinary_fup_txt' => 'Follow up Comments:',

            'avoid_bowel' => 'Patient gets constipated:',
            'avoid_bowel2' => 'If yes, patient defines what constipation means to them and how do they manage it?',
            'avoid_bowel2_2' => 'Comments:',
            'avoid_bowel3' => 'Has there been any change in patient regular bowel habit:',
            'avoid_bowel4' => 'Has there been any change in patient stool consistency:',
            'avoid_bowel5' => 'If yes, how has it changed?',
            'avoid_bowel3_2' => 'Comments:',
            'avoid_bowel_fup_txt' => 'Follow up Comments:',

            'avoid_meds' => 'Number of prescribed medications:',
            'avoid_meds2' => 'Number of over the counter medications:',
            'avoid_meds3' => 'Concerns about regular medication use:',
            'avoid_meds4' => 'Concerns:',
            'avoid_meds_fup_txt' => 'Follow up Comments:',
            'Comments' => 'Comments:',

            'avoid_fatigue' => 'Patient feels exhaustion or fatigue during normal activities:',
            'avoid_fatigue2' => 'How many days in a week does this occur during normal activities:',
            'avoid_fatigue_fup_txt' => 'Follow up Comments:',

            'avoid_strength' => 'Dominant hand used?',
            'avoid_strength2' => 'Trial 1 Result:',
            'avoid_strength3' => 'Trial 2 Result:',
            'avoid_strength4' => 'Trial 3 Result:',
            'avoid_strength5' => '30 second Chair Stand repetitions:',

            'avoid_nutrition1' => 'Weight:',
            'avoid_nutrition1_2' => '',
            'avoid_nutrition1_2_2' => 'Any unplanned weight loss in the last year:',
            'avoid_nutrition1_3' => 'If Yes, estimated amount:',
            'avoid_nutrition1_4' => '',
            'avoid_nutrition2_1' => 'Height:',
            'avoid_nutrition2_2' => '',
            'avoid_nutrition2_2_2' => '',
            'avoid_nutrition3' => 'Waist Circumference:',
            'avoid_nutrition4' => 'Follow Eating Well with Canada’s Food Guide:',
            'avoid_nutrition5' => 'BMI:',
            'avoid_nutrition_fup_txt' => 'Follow up Comments:',

            'avoid_osteo' => 'How much Vitamin D does the patient take:',
            'avoid_osteo1' => '',

            'avoid_pain' => 'Does patient regularly suffer from pain?',
            'avoid_pain2' => 'Pain Scale Score:',
            'avoid_pain_fup_txt' => 'Follow up Comments:',

            'avoid_immunization1_1' => 'Last Flu Shot:',
            'avoid_immunization1_2' => 'Last Flu Shot Date:',
            'avoid_immunization2_1' => 'Last tetanus-diphtheria vaccination:',
            'avoid_immunization2_2' => 'Last tetanus-diphtheria Date:',
            'avoid_immunization3_1' => 'Pneumococcal vaccination:',
            'avoid_immunization3_2' => 'Pneumococcal Date:',
            'avoid_immunization4_1' => 'Shingles vaccination:',
            'avoid_immunization4_2' => 'Shingles vaccination Date:',
            'avoid_immunization5_1' => 'Covid vaccination:',
            'avoid_immunization5_2' => 'Covid vaccination Date:',

            'avoid_dental' => 'Does patient brush your teeth and floss regularly?',
            'avoid_dental2' => 'Do patient see a dentist yearly for regular cleaning?',
            'avoid_dental3' => 'Does patient gums bleed while brushing your teeth?',
            'avoid_dental4' => 'Counselling given?',
            'avoid_dental_fup_txt' => 'Follow up Comments:',


            'avoid_lifestyle' => 'Patient drinks alcohol?',
            'avoid_lifestyle2' => 'If yes, how much?',
            'avoid_lifestyle3' => 'Does patient currently smoke or has ever smoked?',
            'avoid_lifestyle4' => 'How many cigarettes/day?',
            'avoid_lifestyle5' => 'For how many years?',
            'avoid_lifestyle6' => 'Are they thinking of quitting?',
            'avoid_lifestyle7' => 'Are they aware there are resources to support them? ',
            'avoid_lifestyle_fup_txt' => 'Follow up Comments:',

            'avoid_other' => 'Comments:',
        );

        static $special_after_map = array(
            'avoid_balance6' => 's',
            'avoid_balance7' => 'ms',
            'avoid_balance9' => 's',
            'avoid_balance10' => 'ms',
            'avoid_nutrition2_2_2' => 'inches',
            'avoid_nutrition3' => 'cm',
            'avoid_lifestyle2' => ' # of drinks/week',
            'avoid_cognition2' => '%',
            'avoid_cognition2_2' => '%',
            'avoid_cognition3_2' => '%',
            'avoid_cognition4_2' => '%',

        );

        static $special_nospace_after_map = array(
            'avoid_balance6',
            'avoid_balance9',
            'avoid_nutrition1',
            'avoid_nutrition1_3',
            'avoid_nutrition2_1',
            'avoid_nutrition2_2',
            'avoid_osteo'
        );

        static $special_nospace_before_map = array(
            'avoid_balance7',
            'avoid_balance10',
            'avoid_nutrition1_2',
            'avoid_nutrition1_4',
            'avoid_nutrition2_2',
            'avoid_nutrition2_2_2',
            'avoid_osteo1'
        );

        static $fup_map = array(
            'avoid_vision' => 'avoid_vision_fup',
            'avoid_hearing' => 'avoid_hearing_fup',
            'avoid_communication' => 'avoid_communication_fup',
            'avoid_cognition' => 'avoid_cognition5',
            'avoid_dementia' => 'avoid_dementia3_fup',
            'avoid_depression' => 'avoid_depression_fup',
            'avoid_balance3' => 'avoid_balance_fup',
            'avoid_adl' => 'avoid_adl_fup',
            'avoid_iadl' => 'avoid_iadl_fup',
            'avoid_caregiver' => 'avoid_caregiver_fup',
            'avoid_urinary' => 'avoid_urinary_fup',
            'avoid_bowel' => 'avoid_bowel_fup',
            'avoid_meds' => 'avoid_meds_fup',
            'avoid_fatigue' => 'avoid_fatigue_fup',
            'avoid_strength' => false,
            'avoid_nutrition1' => 'avoid_nutrition_fup',
            'avoid_osteo' => 'avoid_osteo_fup',
            'avoid_pain' => 'avoid_pain_fup',
            'avoid_immunization1_1' => false,
            'avoid_dental' => 'avoid_dental_fup',
            'avoid_lifestyle' => 'avoid_lifestyle_fup',
            'avoid_other' => false,
        );

        static $section_map = array(
            'avoid_vision' => '1. VISION',
            'avoid_hearing' => '2. HEARING',
            'avoid_communication' => '3. COMMUNICATION',
            'avoid_cognition' => '4. COGNITION',
            'avoid_dementia' => '5. DEMENTIA QUICK SCREEN',
            'avoid_depression' => '6. DEPRESSION',
            'avoid_balance3' => '7. BALANCE/FALLS/MOBILITY',
            'avoid_adl' => '8. ADL',
            'avoid_iadl' => '9. IADL',
            'avoid_caregiver' => '10. CAREGIVER SUPPORT',
            'avoid_urinary' => '11. URINARY INCONTINENCE',
            'avoid_bowel' => '12. BOWEL',
            'avoid_meds' => '13. MEDICATIONS',
            'avoid_fatigue' => '14. COMPLAINING OF FATIGUE',
            'avoid_strength' => '15. STRENGTH',
            'avoid_nutrition1' => '16. NUTRITION/OBESITY',
            'avoid_osteo' => '17. OSTEOPOROSIS',
            'avoid_pain' => '18. PAIN',
            'avoid_immunization1_1' => '19. IMMUNIZATION',
            'avoid_dental' => '20. DENTAL',
            'avoid_lifestyle' => '21. LIFESTYLE ISSUES',
            'avoid_other' => '22. OTHER',
        );

        function __construct(){
            SpecialPage::__construct("InPersonFollowup", null, true);
        }

        function userCanExecute($wgUser){
            $person = Person::newFromUser($wgUser);
            return $person->isRole('Assessor');
        }

        static function getRow($person, $report, $type = false, $simple = false){
            global $wgServer, $wgScriptPath;
            $html = "";
            $section_number = 0;
            foreach ($report->sections as $section) {
                foreach ($section->items as $item) {
                    if ($item->blobItem != "" && $item->blobItem !== 0) {
                        if (isset(self::$map[$item->blobItem])) {
                            $section_number++;
                            $after = "";
                            if (isset(self::$special_after_map[$item->blobItem])) {
                                $after = self::$special_after_map[$item->blobItem];
                            }
                            if (isset(self::$section_map[$item->blobItem])) { //if this is a new section in the report (did not use sections in xml so have to do this check)
                                if ($section_number > 1) {
                                    $html .= "</table>";
                                }
                                $html .= "<br /><hr /><h1>" . self::$section_map[$item->blobItem] . "</h1></br />";
                                $html .= "<table class='fup_results'>";
                            }
                            $value = $item->getBlobValue();
                            if (!isset($value)) {
                                $html .= "";
                                continue;
                            } elseif ($value == "") {
                                //question label
                                if (self::$map[$item->blobItem] != "") {
                                    $html .= "<tr><td class='label_col'><b>" . self::$map[$item->blobItem] . "</b></td>";
                                }
                                if (!in_array($item->blobItem, self::$special_nospace_before_map)) {
                                    $html .= "<td class='value_col'>";
                                }
                                $html .= "N/A " . $after . " ";
                                if (!in_array($item->blobItem, self::$special_nospace_after_map)) {
                                    $html .= "</td>";
                                }
                            } elseif (is_array($value)) {
                                //question label
                                if (self::$map[$item->blobItem] != "") {
                                    $html .= "<tr><td class='label_col'><b>" . self::$map[$item->blobItem] . "</b></td>";
                                }
                                if (!in_array($item->blobItem, self::$special_nospace_before_map)) {
                                    $html .= "<td class='value_col'>";
                                }
                                $html .= implode(", ", $value) . $after;
                                if (!in_array($item->blobItem, self::$special_nospace_after_map)) {
                                    $html .= "</td></tr>";
                                }
                            } else {
                                //question label
                                if (self::$map[$item->blobItem] != "") {
                                    $html .= "<tr><td class='label_col'><b>" . self::$map[$item->blobItem] . "</b></td>";
                                }
                                if (!in_array($item->blobItem, self::$special_nospace_before_map)) {
                                    $html .= "<td class='value_col'>";
                                }
                                $html .= "{$value} " . $after . " ";
                                if (!in_array($item->blobItem, self::$special_nospace_after_map)) {
                                    $html .= "</td></tr>";
                                }
                            }
                        }
                    }
                }
                $html .= "</table>";
            }
            return $html;
        }

        function execute($par){
            global $wgServer, $wgScriptPath, $wgOut;
            $wgOut->addScript(" <style>
                                    #personHeader{font-size: 160%;
                                        margin-bottom: 11px;
                                        padding-top:30px;
                                        display: block;
                                        margin-left: 10px;
                                        margin-right: 10px;
                                    }
                                    .assess_results{
                                        width:80%;
                                        padding-left:100px;
                                        font-size: 1.2em;
                                        line-height: 1.2em;
                                    }
                                    td.label_col, td.value_col{
                                        border: 0.5px solid gray;
                                        padding:10px;
                                    }
                                    td.label_col{
                                        width:400px;
                                    }
                                    td.value_col{
                                        text-align:right;
                                        min-width:50px;
                                    }
                                    .fup_results{
                                        padding-left:50px;
                                    }
                                </style>");
            $me = Person::newFromWgUser();
            $wgOut->setPageTitle("Assessor");
            $wgOut->addHTML("<span id='pageDescription'>Select a user from the list below to view In-Person Assessment Summary</span><table>
            <tr><td>
                <select id='names' data-placeholder='Choose a Person...' name='name' size='10' style='width:100%'>");
            $rels = $me->getRelations("Assesses");
            foreach ($rels as $rel) {
                $option = $rel->getUser2();
                $wgOut->addHTML("<option value=\"{$option->getId()}\">" . str_replace(".", " ", $option->getNameForForms()) . "</option>\n");
            }
            $wgOut->addHTML("</select>
                </td></tr>
                <tr><td>
                <input type='button' id='button' name='next' value='View In-Person Summary' disabled='disabled' /></td></tr></table>
                <script type='text/javascript'>
                $('#names').chosen();
                $(document).ready(function(){
                $('#names').change(function(){
                    var page = $('#names').val();
                    if(page != ''){
                        $('#button').prop('disabled', false);
                    }
                });
                $('#button').click(function(){
                var page = $('#names').val();
                if(typeof page != 'undefined'){
                    document.location = '" . $wgServer . $wgScriptPath . "/index.php/Special:InPersonFollowup?&personid=' + page;
                }
                });
                });
                </script>");

            if (isset($_GET['personid'])) {
                $personid = $_GET['personid'];
                $person = Person::newFromId($personid);
                $wgOut->setPageTitle("{$person->getNameForForms()} In-Person Assessment Summary");
                $wgOut->setPageTitle(static::$pageTitle);
                $report = new DummyReport(static::$reportName, $me, null, YEAR);
                $wgOut->setPageTitle("Assessor");
                $wgOut->addHTML("<div id='personHeader'><span style='font-size: 200%'>{$person->getNameForForms()}</span><br /><br /><a class='program-button' href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=InPersonAssessment&person={$person->getId()}'>In-Person Assessment Form</a></div>");
                $report->person = $person;
                $content = "<div id='personHeader'><span style='font-size: 200%'>{$person->getNameForForms()} In-Person Assessment Form</div><br />Date: ".date("Y/m/d")."<br />";
                $content .= "<div class='assess_results'>" .
                    InPersonFollowup::getRow($person, $report, false, true) . "</div>";
                $content = htmlspecialchars(urlencode($content));
                $wgOut->addHTML("
    <form action='{$wgServer}{$wgScriptPath}/index.php?action=api.DownloadWordHtmlApi' enctype='multipart/form-data' id='downloadword' method='post' target='_blank'><input type='hidden' name='content' value='{$content}'><input type='hidden' name='filename' value='{$person->getNameForForms()} In-Person Assessment Download'><input type='submit' value='Download Word'></form>");


                $wgOut->addHTML("<div class='assess_results'>" .
                    InPersonFollowup::getRow($person, $report, false, true) . "</div>");
            }

        }

        static function createSubTabs(&$tabs){
            global $wgUser, $wgServer, $wgScriptPath, $wgTitle;
            $me = Person::newFromWgUser();
            if ($me->isRole("Assessor")) {
                $selected = @($wgTitle->getText() == "InPersonFollowup") ? "selected" : false;
                $tabs['InPersonAssessment']['subtabs'][] = TabUtils::createSubTab("Report", "{$wgServer}{$wgScriptPath}/index.php/Special:InPersonFollowup", $selected);
            }
            return true;
        }

        function getBlobData($blobSection, $blobItem, $person, $year, $rpType = null){
            $rpType = ($rpType == null) ? static::$rpType : $rpType;
            $blb = new ReportBlob(BLOB_TEXT, $year, $person->getId(), 0);
            $addr = ReportBlob::create_address($rpType, $blobSection, $blobItem, 0);
            $result = $blb->load($addr);
            return $blb->getData();
        }

    }
?>
