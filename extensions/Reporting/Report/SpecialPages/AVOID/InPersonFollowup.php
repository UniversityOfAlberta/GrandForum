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
            'avoid_vision_fup_recommended' => 'Recommendations',

            'avoid_hearing_fup_recommended' => 'Recommendations',

            'avoid_cognition_fup_recommended' => 'Recommendations',


            'avoid_depression_fup_recommended' => 'Recommendations',

            'avoid_balance_fup_recommended' => 'Recommendations',

            'avoid_adl_fup_recommended' => 'Recommendations',

            'avoid_iadl_fup_recommended' => 'Recommendations',

            'avoid_caregiver_fup_recommended' => 'Recommendations',

            'avoid_urinary_recommended' => 'Recommendations',

            'avoid_bowel_fup_recommended' => 'Recommendations',

            'avoid_meds_fup_recommended' => 'Recommendations',

            'avoid_fatigue_fup_recommended' => 'Recommendations',

            'avoid_strength_fup_recommended' => 'Recommendations',

            'avoid_nutrition_fup_recommended' => 'Recommendations',

            'avoid_pain_fup_recommended' => 'Recommendations',

            'avoid_immunization_fup_recommended' => 'Recommendations',

            'avoid_dental_fup_recommended' => 'Recommendations',

            'avoid_lifestyle_fup_recommended' => 'Recommendations',

            'avoid_chronic_recommended' => 'Recommendations',

        );

        static $special_after_map = array(
        );

        static $special_nospace_after_map = array(
        );

        static $special_nospace_before_map = array(
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
            'avoid_vision_fup_recommended' => '1. VISION',
            'avoid_hearing_fup_recommended' => '2. HEARING',
            'avoid_cognition_fup_recommended' => '4. COGNITION',
            'avoid_depression_fup_recommended' => '6. DEPRESSION',
            'avoid_balance_fup_recommended' => '7. BALANCE/FALLS/MOBILITY',
            'avoid_adl_fup_recommended' => '8. ADL',
            'avoid_iadl_fup_recommended' => '9. IADL',
            'avoid_caregiver_fup_recommended' => '10. CAREGIVER SUPPORT',
            'avoid_urinary_recommended' => '11. URINARY INCONTINENCE',
            'avoid_bowel_fup_recommended' => '12. BOWEL',
            'avoid_meds_fup_recommended' => '13. MEDICATIONS',
            'avoid_fatigue_fup_recommended' => '14. COMPLAINING OF FATIGUE',
            'avoid_strength_fup_recommended' => '15. STRENGTH',
            'avoid_nutrition_fup_recommended' => '16. NUTRITION/OBESITY',
            'avoid_pain_fup_recommended' => '18. PAIN',
            'avoid_immunization_fup_recommended' => '19. IMMUNIZATION',
            'avoid_dental_fup_recommended' => '20. DENTAL',
            'avoid_lifestyle_fup_recommended' => '21. LIFESTYLE ISSUES',
            'avoid_chronic_recommended' => '22. CHRONIC DISEASE',
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
                        if (@isset(self::$map[$item->blobItem])) {
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
        
        static function getContent($person){
            $report = new DummyReport(static::$reportName, $person, null, YEAR);
            $content = "<div id='personHeader'><span style='font-size: 200%'>{$person->getNameForForms()} In-Person Assessment Form</div><br />Date: ".date("Y/m/d")."<br />";
            $content .= "<div class='assess_results'>" .
                InPersonFollowup::getRow($person, $report, false, true) . "</div>";
            $content = htmlspecialchars(urlencode($content));
            return $content;
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
                $report = new DummyReport(static::$reportName, $person, null, YEAR);
                $wgOut->setPageTitle("Assessor");
                $wgOut->addHTML("<div id='personHeader'><span style='font-size: 200%'>{$person->getNameForForms()}</span><br /><br /><a class='program-button' href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=InPersonAssessment&person={$person->getId()}'>In-Person Assessment Form</a></div>");
                $content = self::getContent($person);
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
