<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['IntakeSummary'] = 'IntakeSummary'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['IntakeSummary'] = $dir . 'IntakeSummary.i18n.php';
$wgSpecialPageGroups['IntakeSummary'] = 'reporting-tools';

$wgHooks['SubLevelTabs'][] = 'IntakeSummary::createSubTabs';

function runIntakeSummary($par) {
    IntakeSummary::execute($par);
}

require_once("EQ5D5L.php");

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
        'employment_avoid' => 'Employment (Single)',
        'employments_avoid' => 'Employment (Multiple)',
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
    
    static $topics = array("IngredientsForChange" => "Ingredients For Change",
                           "Activity" => "Activity",
                           "Vaccination" => "Vaccination",
                           "OptimizeMedication" => "Optimize Medication",
                           "Interact" => "Interact",
                           "DietAndNutrition" => "Diet And Nutrition",
                           "Sleep" => "Sleep",
                           "FallsPrevention" => "Falls Prevention");
    
    function __construct() {
        SpecialPage::__construct("IntakeSummary", null, true, 'runIntakeSummary');
    }
    
    function userCanExecute($wgUser){
        $person = Person::newFromUser($wgUser);
        return $person->isRoleAtLeast(STAFF);
    }
    
    static function getHeader($report, $type=false, $simple=false){
        global $config;
        $html = "";
        if(!$simple){
            $html = "<thead>
                        <tr>
                            <th>User Id</th>
                            <th>".Inflect::pluralize($config->getValue('subRoleTerm'))."</th>";
        }
        if($type != false){
            $html .= "<th>Type</th>";
        }
        if(static::$rpType != "RP_AVOID_THREEMO" && static::$rpType != "RP_AVOID_NINEMO"){
            $html .= "<th>Frailty Score</th>";
            $html .= "<th>EQ Health State</th>";
            $html .= "<th>EQ Health Score</th>";
            $html .= "<th>VAS Score</th>";
            $html .= "<th>CFS Score</th>";
        }
        $html .= "<th>Usage</th>";
        $html .= "<th>Month Registered</th>";
        $html .= "<th>Hear about us</th>";
        foreach($report->sections as $section){
            foreach($section->items as $item){
                if($item->blobItem != "" && $item->blobItem !== 0){
                    $label = (isset(self::$map[$item->blobItem])) ? self::$map[$item->blobItem] : str_replace("_", " ", $item->blobItem);
                    $html .= "<th>{$label}</th>";
                }
            }
        }
        if(!$simple){                      
            $html .= "  </tr>
                      </thead>";
        }
        return $html;
    }
    
    static function getRow($person, $report, $type=false, $simple=false){
        global $wgServer, $wgScriptPath, $config, $EQ5D5L;
        $me = Person::newFromWgUser();
        $registration_str = $person->getRegistration();
        $registration_date = substr($registration_str,0,4)."-".substr($registration_str,4,2);

        $hear = implode("<br />", array_filter(array($person->getExtra('hearField', ''),
                                                     $person->getExtra('hearLocationSpecify'),
                                                     $person->getExtra('hearPlatformSpecify'),
                                                     $person->getExtra('hearPlatformOtherSpecify'),
                                                     $person->getExtra('hearProgramOtherSpecify'))));
        
        $hear = ($hear == "") ? implode("<br />", array_filter(array(self::getBlobData("AVOID_Questions_tab0", "program_avoid", $person, YEAR, "RP_AVOID"), 
                                                                     self::getBlobData("AVOID_Questions_tab0", "PROGRAMLOCATIONSPECIFY", $person, YEAR, "RP_AVOID"),
                                                                     self::getBlobData("AVOID_Questions_tab0", "platform_avoid", $person, YEAR, "RP_AVOID"),
                                                                     self::getBlobData("AVOID_Questions_tab0", "PROGRAMPLATFORMOTHERSPECIFY", $person, YEAR, "RP_AVOID"),
                                                                     self::getBlobData("AVOID_Questions_tab0", "PROGRAMOTHERSPECIFY", $person, YEAR, "RP_AVOID")))) : $hear;
        
        $userLink = "{$person->getId()}";
        if($type == false){
            $userLink = "<a class='userLink' href='{$wgServer}{$wgScriptPath}/index.php/Special:IntakeSummary?users={$person->getId()}'>{$person->getId()}</a>";
        }
        $contact = ($me->isRole(ADMIN)) ? "\n<br /><a href='#' class='viewContact'>Contact</a>" : "" ;
        $html = "";
        if(!$simple){
            $subRoles = array();
            foreach(@$person->getSubRoles() as $sub){
                $subRoles[] = $config->getValue('subRoles', $sub);
            }
            if(empty($subRoles)){
                $subRoles[] = "online independent";
            }
            $html = "<tr data-id='{$person->getId()}'>
                        <td>{$userLink}{$contact}</td>
                        <td style='white-space:nowrap;' align='left'>".implode(",<br />", $subRoles)."</td>";
        }
        if($type != false){
            $html .= "<td style='white-space:nowrap;'>{$type}</td>";
        }
        if(static::$rpType != "RP_AVOID_THREEMO" && static::$rpType != "RP_AVOID_NINEMO"){
            $api = new UserFrailtyIndexAPI();
            if($report->reportType == "RP_AVOID_THREEMO"){
                $scores = $api->getFrailtyScore($person->getId(), "RP_AVOID");
            }
            else if($report->reportType == "RP_AVOID_NINEMO"){
                $scores = $api->getFrailtyScore($person->getId(), "RP_AVOID_SIXMO");
            }
            else{
                $scores = $api->getFrailtyScore($person->getId(), $report->reportType);
            }
            $html .= "<td>".number_format($scores["Total"]/36, 3)."</td>";
            $html .= "<td>".implode("", $scores["Health"])."</td>";
            $html .= "<td>".$EQ5D5L[implode("", $scores["Health"])]."</td>";
            $html .= "<td>".$scores["VAS"]."</td>";
            $html .= "<td>".$scores["CFS"]."</td>";
        }
        $html .= "<td align='center'><a href='#' class='viewUsage'>View</a></td>";
        $html .= "<td align='center'>{$registration_date}</td>";
        $html .= "<td>{$hear}</td>";
        $hasSubmitted = AVOIDDashboard::hasSubmittedSurvey($person->getId(), $report->reportType);
        foreach($report->sections as $section){
            foreach($section->items as $item){
                if($item->blobItem != "" && $item->blobItem !== 0){
                    $value = ($hasSubmitted) ? $item->getBlobValue() : "";
                    if(is_array($value)){
                        $html .= "<td>".implode(", ", $value)."</td>";
                    }
                    else{
                        $html .= "<td>{$value}</td>";
                    }
                }
            }
        }
        if(!$simple){
            $html .= "</tr>";
        }
        return $html;
    }
    
    function userTable(){
        global $wgOut;
        $me = Person::newFromWgUser();
        $report = new DummyReport(IntakeSummary::$reportName, $me, null, YEAR);

        $people = array();
        foreach(explode(",", $_GET['users']) as $id){
            $person = Person::newFromId($id);
            $people[] = $person;
            
            $wgOut->addHTML($this->dataCollectionTable($person));
            if($me->isRoleAtLeast(ADMIN)){
                $wgOut->addHTML($this->contactTable($person));
            }
        }
        
        $wgOut->addHTML("<table id='summary' class='wikitable'>");
        $wgOut->addHTML(self::getHeader($report, true, false));
        $wgOut->addHTML("<tbody>");
        
        foreach($people as $person){
            if($person->getId() == 0){
                continue;
            }
            $report->person = $person;
            $report->reportType = "RP_AVOID";
            $wgOut->addHTML(self::getRow($report->person, $report, "Intake"));
            $report->reportType = "RP_AVOID_THREEMO";
            $wgOut->addHTML(self::getRow($report->person, $report, "3 Month"));
            $report->reportType = "RP_AVOID_SIXMO";
            $wgOut->addHTML(self::getRow($report->person, $report, "6 Month"));
            $report->reportType = "RP_AVOID_NINEMO";
            $wgOut->addHTML(self::getRow($report->person, $report, "9 Month"));
            $report->reportType = "RP_AVOID_TWELVEMO";
            $wgOut->addHTML(self::getRow($report->person, $report, "12 Month"));
        }
        $wgOut->addHTML("</tbody>
                        </table>");
    }
    
    static function usageHeaderTop(){
        return "<th rowspan='2'>Action Plans</th>
                <th colspan='".count(self::$topics)."'>Education</th>
                <th colspan='4'>Data Collected</th>";
    }
    
    static function usageHeaderBottom(){
        return "<th style='width:1px;'>".implode("</th><th style='width:1px;'>", self::$topics)."</th>
                <th style='width:1px;'>Program Library</th>
                <th style='width:1px;'>Frailty Report Views</th>
                <th style='width:1px;'>Progress Report Views</th>
                <th style='width:1px;'>Logins</th>";
    }
    
    static function programAttendanceHeaderTop(){
        return "<th colspan='".count(array_column(AdminDataCollection::$programs, 'text'))."'>Program Attendance</th>";
    }
    
    static function programAttendanceHeaderBottom(){
        return "<th>".implode("</th><th>", array_column(AdminDataCollection::$programs, 'text'))."</th>";
    }
    
    static function usageRow($person){
        $html = "";
        // Action Plans
        $plans = array();
        foreach(ActionPlan::newFromUserId($person->getId()) as $plan){
            $plans[] = $plan;
        }
        
        $submittedPlans = array();
        $components = array('A' => 0, 
                            'V' => 0, 
                            'O' => 0, 
                            'I' => 0, 
                            'D' => 0, 
                            'S' => 0, 
                            'F' => 0);
        foreach($plans as $plan){
            foreach($plan->getComponents() as $comp => $val){
                if($val == 1){
                    @$components[$comp]++;
                }
            }
            if($plan->getSubmitted()){
                $submittedPlans[] = $plan;
            }
        }
        
        $html .= "<td style='white-space:nowrap;'><b>Created:</b> ".count($plans)."<br /><b>Submitted:</b> ".count($submittedPlans)."<br /></td>";
    
        $resource_data = DBFunctions::select(array('grand_data_collection'),
                                             array('*'),
                                             array('user_id' => $person->getId()));

        // Topics
        foreach(self::$topics as $key => $topic){
            $html .= "<td style='padding:0;' valign='top'>";
            foreach($resource_data as $page){
                $page_name = trim($page["page"]);
                $page_data = json_decode($page["data"], true);
                if($page_name == $key){
                    $html .= "<table class='wikitable' style='border-collapse: collapse; table-layout: auto; width: 100%; margin-top:0px; margin-bottom:0;'>";
                    foreach($page_data as $key => $value){
                        if(strlen($key) < 4){
                            continue;
                        }
                        if(is_array($value)){
                            continue;
                            $value = implode("|",$value);
                        }
                        $key = str_replace("PageCount", " Views", $key);
                        $key = str_replace("Time", " Time", $key);
                        if(strpos($key, "Time")){
                            $init = $value;
                            $hours = floor($init / 3600);
                            $minutes = floor(($init / 60) % 60);
                            $seconds = $init % 60;
                            $value = "$hours:$minutes:$seconds";
                        }
                        $html .= "<tr style=''><td nowrap>$key:</td> <td align='right'>$value</td></tr>\n";
                    }
                    $html .= "</table>";
                    break;
                }
            }
            $html .= "</td>";
        }
            
        // Program Library
        $html .= "<td style='padding:0;' valign='top'><table class='wikitable' style='border-collapse: collapse; table-layout: auto; width: 100%; margin-top:0px; margin-bottom:0;'>";
        foreach($resource_data as $page){
            $page_name = trim($page["page"]);
            if(strstr($page_name, "ProgramLibrary") !== false){
                $page_name = str_replace("ProgramLibrary-", "", trim($page["page"]));
                $page_data = json_decode($page["data"],true);
                $views = isset($page_data["pageCount"]) ? $page_data["pageCount"] : 0;
                $websiteClicks = isset($page_data["websiteClicks"]) ? $page_data["websiteClicks"] : 0;
                $html .= "<tr><td style='white-space:nowrap;'>$page_name</td> <td nowrap>Views: $views</td></tr>\n";
            }
        }
            
        // Links
        $html .= "</table></td>";
        $fviews = 0;
        $ftime = 0;
        $pviews = 0;
        $ptime = 0;
        $logins = 0;
        foreach($resource_data as $page){
            $page_name = trim($page["page"]);
            $page_data = json_decode($page["data"],true);
            if(strstr($page_name, "FrailtyReport") !== false){
                $fviews += isset($page_data["count"]) ? $page_data["count"] : (isset($page_data["hits"]) ? $page_data["hits"] : 0);
                $ftime += @$page_data["time"];
            }
            else if(strstr($page_name, "ProgressReport") !== false){
                $pviews += isset($page_data["count"]) ? $page_data["count"] : (isset($page_data["hits"]) ? $page_data["hits"] : 0);
                $ptime += @$page_data["time"];
            }
            else if(strstr($page_name, "loggedin") !== false){
                $logins += count($page_data["log"]);
            }
        }
        $html .= "<td align='right'>$fviews</td>";
        if(AVOIDDashboard::hasSubmittedSurvey($person->getId(), "RP_AVOID_THREEMO")){
            $html .= "<td align='right'>$pviews</td>";
        }
        else{
            $html .= "<td align='right'>N/A</td>";
        }
        $html .= "<td align='right'>$logins</td>";
        return $html;
    }
    
    static function programAttendanceRow($person){
        $html = "";
        foreach(AdminDataCollection::$programs as $key => $program){
            $checked = array((IntakeSummary::getBlobData("ATTENDANCE", "{$key}", $person, 0, "RP_SUMMARY") == 1) ? "checked" : "");
            $span = array();
            for($i=1;$i<$program['count'];$i++){
                $checked[] = (IntakeSummary::getBlobData("ATTENDANCE", "{$key}_$i", $person, 0, "RP_SUMMARY") == 1) ? "checked" : "";
            }
            
            foreach($checked as $check){
                $span[] = ($check != "") ? "Yes" : "No";
            }
        
            $date = array(IntakeSummary::getBlobData("ATTENDANCE", "{$key}_date", $person, 0, "RP_SUMMARY"));
            for($i=1;$i<$program['count'];$i++){
                $date[] = IntakeSummary::getBlobData("ATTENDANCE", "{$key}_{$i}_date", $person, 0, "RP_SUMMARY");
            }
            $html .= "<td align='left' style='width:1px;white-space:nowrap;'>";
            for($i=0;$i<$program['count'];$i++){
                $html .= "{$span[$i]}";
                if($date[$i] != ""){
                    $html .= ": {$date[$i]}";
                }
                $html .= "<br />";
            }
            $html .= "</td>";
        }
        return $html;
    }
    
    function dataCollectionTable($person){
        global $wgServer, $wgScriptPath;
        $html = "<div id='data_{$person->getId()}' style='display:none;'><table class='wikitable data_collection' cellpadding='5' cellspacing='1' style='width:100%;'>
                    <thead>
                        <tr>".self::usageHeaderTop()."</tr>
                        <tr>".self::usageHeaderBottom()."</tr>
                    </thead>
                    <tbody>";
        
        $html .= "<tr>".self::usageRow($person)."</tr>";
        $html .= "</tbody></table>";
        
        // Programs
        $html .= "<table class='wikitable program_attendance' cellpadding='5' cellspacing='1' style='width:100%;'>
                    <thead>
                        <tr>".self::programAttendanceHeaderTop()."</tr>
                        <tr>".self::programAttendanceHeaderBottom()."</tr>
                    </thead>
                    <tbody>
                        <tr>".self::programAttendanceRow($person)."</tr>
                    </tbody>
                  </table>";
        $html .= "</div>";
        return $html;
    }
    
    function contactTable($person){
        $name = $person->getNameForForms();
        $email = $person->getEmail();
        $phone = $person->getExtra('phone', $person->getPhoneNumber());
        
        $html = "<div id='contact_{$person->getId()}' style='display:none;'>
                    <table cellpadding='0' cellspacing='0'>
                        <tr>
                            <td class='label'>Name:</td>
                            <td>{$name}</td>
                        </tr>
                        <tr>
                            <td class='label'>Email:</td>
                            <td>{$email}</td>
                        </tr>
                        <tr>
                            <td class='label'>Phone#:</td>
                            <td>{$phone}</td>
                        </tr>
                    </table>
                 </div>";
        return $html;
    }
    
    function execute($par){
        global $wgServer, $wgScriptPath, $wgOut;
        if(isset($_GET['users'])){
            $this->userTable();
        }
        else{
            $me = Person::newFromWgUser();
            $wgOut->setPageTitle(static::$pageTitle);
            $people = Person::getAllPeople(CI);
            
            $report = new DummyReport(static::$reportName, $me, null, YEAR);
            foreach($people as $person){
                if(!$person->isRoleAtMost(CI)){
                    continue;
                }
                if(AVOIDDashboard::hasSubmittedSurvey($person->getId(), static::$rpType) && $this->getBlobData("AVOID_Questions_tab0", "POSTAL", $person, YEAR, "RP_AVOID") != "CFN"){
                    $wgOut->addHTML($this->dataCollectionTable($person));
                    if($me->isRoleAtLeast(ADMIN)){
                        $wgOut->addHTML($this->contactTable($person));
                    }
                }
            }
            
            $wgOut->addHTML("<table id='summary' class='wikitable'>");
            $wgOut->addHTML(self::getHeader($report));
            $wgOut->addHTML("<tbody>");
            
            foreach($people as $person){
                if(!$person->isRoleAtMost(CI)){
                    continue;
                }
                if(AVOIDDashboard::hasSubmittedSurvey($person->getId(), static::$rpType) && $this->getBlobData("AVOID_Questions_tab0", "POSTAL", $person, YEAR, "RP_AVOID") != "CFN"){
                    $report->person = $person;
                    $wgOut->addHTML(self::getRow($person, $report));
                }
            }
            $wgOut->addHTML("</tbody>
                            </table>");
        }
        $wgOut->addHTML("
        <iframe id='programAttendanceFrame' name='programAttendanceFrame' style='display:none;'></iframe>
        <div id='usageDialog' style='display:none;'></div>
        <div id='contactDialog' style='display:none;'></div>
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
            
            $('#summary_length').append(\"<button id='compare' style='margin-left:1em;'>Compare visible users</button>\");
            
            $('#compare').click(function(){
                var ids = [];
                $('#summary tbody tr td:first-child a.userLink').each(function(i, el){
                    ids.push($(el).text());
                });
                ids = ids.join(',');
                document.location = wgServer + wgScriptPath + '/index.php/Special:IntakeSummary?users=' + ids;
            });
            
            $('.viewUsage').click(function(){
                var id = $(this).closest('tr').attr('data-id');
                $('#usageDialog').html($('#data_' + id).html());
                $('#usageDialog').dialog({
                    width: 'auto',
                    height: 'auto',
                    title: 'User ' + id + ' Usage Data',
                    buttons: {
                        'Excel' : {
                            text: 'Download as Excel',
                            class: 'downloadExcel',
                            click: function(){
                                window.open('data:application/vnd.ms-excel,' + $('.data_collection', '#usageDialog')[0].outerHTML + 
                                                                               $('.program_attendance', '#usageDialog')[0].outerHTML);
                            }
                        },
                        'Cancel' : function(){
                            $(this).dialog('close');
                        }
                    }
                });
            });
            
            $('.viewContact').click(function(){
                var id = $(this).closest('tr').attr('data-id');
                $('#contactDialog').html($('#contact_' + id).html());
                $('#contactDialog').dialog({
                    width: 'auto',
                    height: 'auto',
                    title: 'User ' + id + ' Contact Information'
                });
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
    
    static function getBlobData($blobSection, $blobItem, $person, $year, $rpType=null){
        $rpType = ($rpType == null) ? static::$rpType : $rpType;
        $blb = new ReportBlob(BLOB_TEXT, $year, $person->getId(), 0);
        $addr = ReportBlob::create_address($rpType, $blobSection, $blobItem, 0);
        $result = $blb->load($addr);
        return $blb->getData();
    }
    
    static function saveBlobData($value, $blobSection, $blobItem, $person, $year, $rpType=null){
        $rpType = ($rpType == null) ? static::$rpType : $rpType;
        $blb = new ReportBlob(BLOB_TEXT, $year, $person->getId(), 0);
        $addr = ReportBlob::create_address($rpType, $blobSection, $blobItem, 0);
        $blb->store($value, $addr);
    }
    
}

?>
