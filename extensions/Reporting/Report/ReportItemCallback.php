<?php

class ReportItemCallback {
    
    static $reportCallback;
    
    static function call($reportItem, $func, $args=null){
        self::$reportCallback->reportItem = $reportItem;
        if($args == null){
            return @self::$reportCallback->{self::$callbacks[$func]}();
        }
        else{
            return @call_user_func_array(array(self::$reportCallback, self::$callbacks[$func]), $args);
        }
    }
    
    static $callbacks = 
        array(
            // Dates
            "startYear" => "getStartYear",
            "start_year" => "getStartYear",
            "2_years_ago" => "get2YearsAgo",
            "last_year" => "getLastYear",
            "this_year" => "getThisYear",
            "next_year" => "getNextYear",
            "endYear" => "getThisYear",
            "start_date" => "getStartDate",
            "end_date" => "getEndDate",
            // Courses
            "getCourseCount" => "getCourseCount",
            "course_term" => "getCourseTerm",
            "course_start" => "getCourseStart",
            "course_end" => "getCourseEnd",
            "course_subject" => "getCourseSubject",
            "course_number" => "getCourseNumber",
            "course_title" => "getCourseTitle",
            "course_comp" => "getCourseComp",
            "course_section" => "getCourseSection",
            "course_percent" => "getCoursePercent",
            "course_calendar" => "getCourseCalendar",
            "course_enroll" => "getCourseEnroll",    
            "course_enroll_percent" => "getCourseEnrollPercent",
            "course_eval" => "getCourseEval",
            "course_eval_summary" => "getCourseEvalSummary",
            "course_eval_n" => "getCourseEvalN",
            "course_eval_enrolled" => "getCourseEvalEnrolled",
            "course_eval_responses" => "getCourseEvalResponses",
            "getCourseAllLectureEnroll" => "getCourseAllLectureEnroll",
            "getUserSectionCounts" => "getUserSectionCounts",
            "getAverageCourseEvalByTerm" => "getAverageCourseEvalByTerm",
            // Student Relation
            "hqp_name" => "getHqpName",
            "hqp_reversed_name" => "getHqpReversedName",
            "hqp_rel_position" => "getHqpRelPosition",
            "hqp_position" => "getHqpPosition",
            "hqp_awards" => "getHqpAwards",
            "user_hqp_role" => "getUserHqpRole",
            "hqp_start_date" => "getHqpStartDate",
            "hqp_end_date" => "getHqpEndDate",
            "hqp_status" => "getHqpStatus",
            "hqp_research_area" => "getHqpResearchArea",
            // Grants
            "grant_id" => "getGrantId",
            "grant_title" => "getGrantTitle",
            "grant_scientific_title" => "getGrantScientificTitle",
            "grant_description" => "getGrantDescription",
            "grant_title_both" => "getGrantTitleBoth",
            "grant_sponsor" => "getGrantSponsor",
            "grant_project_id" => "getGrantProjectId",
            "grant_start_date" => "getGrantStartDate",
            "grant_end_date" => "getGrantEndDate",
            "grant_total" => "getGrantTotal",
            "grant_my_portion" => "getGrantMyPortion",
            "grant_average" => "getGrantAverage",
            "grant_is_pi" => "isGrantPI",
            "grant_people" => "getGrantPeople",
            // Outputs
            "output_id" => "getOutputId",
            "output_title" => "getOutputTitle",
            // Reports
            "date" => "getDate",
            "timestamp" => "getTimestamp",
            "section_title" => "getSectionTitle",
            "post_id" => "getPostId",
            "report_name" => "getReportName",
            "report_xmlname" => "getReportXMLName",
            "report_type" => "getReportType",
            "section_name" => "getSectionName",
            "report_has_started" => "getReportHasStarted",
            // People
            "my_id" => "getMyId",
            "my_name" => "getMyName",
            "my_first_name" => "getMyFirstName",
            "my_last_name" => "getMyLastName",
            "my_dept" => "getMyDept",
            "my_roles" => "getMyRoles",
            "my_roles_during" => "getMyRolesDuring",
            "my_sub_roles" => "getMySubRoles",
            "parent_id" => "getParentId",
            "parent_name" => "getParentName",
            "parent_uni" => "getParentUni",
            "user_name" => "getUserName",
            "user_url" => "getUserUrl",
            "user_email" => "getUserEmail",
            "user_phone" => "getUserPhone",
            "user_profile" => "getUserProfile",
            "user_reversed_name" => "getUserReversedName",
            "user_last_name" => "getUserLastName",
            "user_first_name" => "getUserFirstName",
            "user_id" => "getUserId",
            "user_photo" => "getUserPhoto",
            "user_website" => "getUserWebsite",
            "user_ldap" => "getUserLdap",
            "user_roles" => "getUserRoles",
            "user_full_roles" => "getUserFullRoles",
            "user_sub_roles" => "getUserSubRoles",
            "user_uni_start" => "getUserUniStart",
            "user_uni_end" => "getUserUniEnd",
            "user_level" => "getUserLevel",
            "user_title" => "getUserTitle",
            "user_dept" => "getUserDept",
            "user_uni" => "getUserUni",
            "user_depts" => "getUserDepts",
            "user_research_area" => "getUserResearchArea",
            "user_fec" => "getUserFEC",
            "user_case_number" => "getUserCaseNumber",
            "user_keywords" => "getUserKeywords",
            "user_supervisors" => "getUserSupervisors",
            "user_cosupervisors" => "getUserCoSupervisors",
            "getUserHQPGraduatedCount" => "getUserHQPGraduatedCount",
            "getUserHQPCalendarCount" => "getUserHQPCalendarCount",
            "user_grad_count" => "getUserGradCount",
            "user_msc_count" => "getUserMscCount",
            "user_phd_count" => "getUserPhdCount",
            "user_fellow_count" => "getUserFellowCount",
            "user_tech_count" => "getUserTechCount",
            "user_ugrad_count" => "getUserUgradCount",
            "user_other_count" => "getUserOtherCount",
            "user_committee_count" => "getUserCommitteeCount",
            "getHQPCount" => "getHQPCount",
            "getTermsArray" => "getTermsArray",
            "getUserCoursesEnrolledByTerm" => "getUserCoursesEnrolledByTerm",
            "user_courses_count" => "getUserCoursesCount",
            "user_student_count" => "getUserStudentCount",
            "user_lectures_count" => "getUserLecCount",
            "user_lectures_student_count" => "getUserLecStudentCount",
            "user_grant_count" => "getUserGrantCount",
            "user_cv_grant_count" => "getUserCVGrantCount",
            "user_grant_total" => "getUserGrantTotal",
            "user_phd_year" => "getUserPhdYear",
            "user_phd_date" => "getUserPhDDate",
            "user_phd_uni" => "getUserPhDUni",
            "user_appointment_year" => "getUserAppointmentYear",
            "user_appointment_date" => "getUserAppointmentDate",
            "user_ats_year" => "getATSYear",
            "user_ats_start_date" => "getATSStartDate",
            "user_ats_end_date" => "getATSEndDate",
            "milestone_id" => "getMilestoneId",
            "getFecPersonalInfo" => "getFecPersonalInfo",
            "getUserPublicationCount" => "getUserPublicationCount",
            "getUserServiceCount" => "getUserServiceCount",
            "user_lifetime_pubs_count" => "getUserLifetimePublicationCount",
            "isAllowedToViewRecommendation" => "isAllowedToViewRecommendation",
            "getPersonVar" => "getPersonVar",
            // Chair
            "chair_id" => "getChairId",
            // Products
            "product_id" => "getProductId",
            "product_type" => "getProductType",
            "product_title" => "getProductTitle",
            "product_status" => "getProductStatus",
            "product_authors" => "getProductAuthors",
            "product_contributors" => "getProductContributors",
            "product_description" => "getProductDescription",
            "product_url" => "getProductUrl",
            "product_citation" => "getProductCitation",
            "product_qa_citation" => "getProductQACitation",
            "product_date" => "getProductDate",
            "product_acceptance_year" => "getProductAcceptanceYear",
            "product_year" => "getProductYear",
            "product_year_range" => "getProductYearRange",
            "product_date_range" => "getProductDateRange",
            "product_scope" => "getProductScope",
            "getProductData" => "getProductData",
            //Presentations
            "presentation_title" => "getPresentationTitle",
            "presentation_type" => "getPresentationType",
            "presentation_invited" => "getPresentationInvited",
            "presentation_organization" => "getPresentationOrganization",
            "presentation_country" => "getPresentationCountry",
            "presentation_length" => "getPresentationLength",
            "presentation_date" => "getProductDate",
            //Awards
            "award_scope" => "getAwardScope",
            "award_by" => "getAwardedBy",
            "award_year_range" => "getAwardYearRange",
            // Other
            "wgUserId" => "getWgUserId",
            "wgServer" => "getWgServer",
            "wgScriptPath" => "getWgScriptPath",
            "GET" => "getGet",
            "networkName" => "getNetworkName",
            "id" => "getId",
            "strtotime" => "strtotime",
            "date_format" => "date_format",
            "time2date" => "time2date",
            "name" => "getName",
            "i" => "getI",
            "index" => "getIndex",
            "value" => "getValue",
            "extraIndex" => "getExtraIndex",
            "getNProducts" => "getNProducts",
            "getBlobMD5" => "getBlobMD5",
            "getText" => "getText",
            "getNumber" => "getNumber",
            "getHTML" => "getHTML",
            "getArray" => "getArray",
            "getExtra" => "getExtra",
            "getIncrement" => "getIncrement",
            "removeSpaces" => "removeSpaces",
            "replace" => "replace",
            "strtolower" => "strtolower",
            "strtoupper" => "strtoupper",
            "substr" => "substr",
            "alpha" => "alpha",
            "nl2br" => "nl2br",
            "strip_html" => "strip_html",
            "occurrences" => "occurrences",
            "count" => "count",
            "add" => "add",
            "subtract" => "subtract",
            "multiply" => "multiply",
            "divide" => "divide",
            "round" => "round",
            "max" => "max",
            "min" => "min",
            "set" => "set",
            "get" => "get",
            "if" => "ifCond",
            "and" => "andCond",
            "or" => "orCond",
            "contains" => "contains",
            "!contains" => "notContains",
            "==" => "eq",
            "!=" => "neq",
            ">" => "gt",
            "<" => "lt",
            ">=" => "gteq",
            "<=" => "lteq",
        );
    
    var $reportItem;
    
    // Constructor
    function __construct($reportItem){
        $this->reportItem = $reportItem;
    }
    
    function get2YearsAgo(){
        return intval($this->reportItem->getReport()->year)-2;
    }
    
    function getStartYear(){
        return $this->reportItem->getReport()->startYear;
    }
    
    function getLastYear(){
        return intval($this->reportItem->getReport()->year)-1;
    }
    
    function getThisYear(){
        return $this->reportItem->getReport()->year;
    }
    
    function getNextYear(){
        return intval($this->reportItem->getReport()->year)+1;
    }
    
    function getNextYear2(){
        return intval($this->reportItem->getReport()->year)+2;
    }
    
    function getStartDate(){
        if($this->reportItem->getReport()->startMonth == "01-01"){
            return "{$this->getThisYear()}-{$this->reportItem->getReport()->startMonth}";
        }
        return "{$this->getLastYear()}-{$this->reportItem->getReport()->startMonth}";
    }
    function getEndDate(){
        return "{$this->getThisYear()}-{$this->reportItem->getReport()->endMonth}";
    }
    
    function getHqpName(){
        $relation = Relationship::newFromId($this->reportItem->projectId);
        $hqp = $relation->getUser2();
        return $hqp->getNameForForms();
    }
    
    function getHqpReversedName(){
        $relation = Relationship::newFromId($this->reportItem->projectId);
        $hqp = $relation->getUser2();
        return $hqp->getReversedName();
    }
    
    function getHqpRelPosition(){
        return $this->reportItem->milestoneId;
    }

    function getHqpPosition(){
        $relation = Relationship::newFromId($this->reportItem->projectId);
        $hqp = $relation->getUser2();
        return $hqp->getPosition();
    }
    
    function getHqpAwards(){
        $relation = Relationship::newFromId($this->reportItem->projectId);
        $hqp = $relation->getUser2();
        $award_names = array();
        $awards = $hqp->getPapers("Award", false, 'both', true, "Public");
        foreach($awards as $award){
            $award_names[] = str_replace("Misc: ", "", str_replace("Other: ", "", $award->type));
        }
        return implode(",",$award_names);
    }
    
    function getUserHqpRole(){
        $relation = Relationship::newFromId($this->reportItem->projectId);
        switch($relation->type){
            case SUPERVISES:
                return "Supervisor";
                break;
            case CO_SUPERVISES:
                return "Co-Supervisor";
                break;
        }
        return $relation->type;
    }
   
    function getHqpStartDate(){
        $relation = Relationship::newFromId($this->reportItem->projectId);
        $array = explode(" ", $relation->getStartDate());
        return str_replace(ZOT, "", $array[0]);
    }
    
    function getHqpEndDate(){
        $relation = Relationship::newFromId($this->reportItem->projectId);
        $array = explode(" ", $relation->getEndDate());
        return str_replace(ZOT, "", $array[0]);
    }
    
    function getHqpResearchArea(){
        $relation = Relationship::newFromId($this->reportItem->projectId);
        $hqp = $relation->getUser2();
        return $hqp->getResearchArea();
    }

    function getHqpStatus(){
        if($this->getHqpEndDate() == ZOT || $this->getHqpEndDate() == ''){
            return "Continuing";
        }
        else{
            $status = "Completed";
            $relation = Relationship::newFromId($this->reportItem->projectId);
            $hqp = $relation->getUser2()->getId();
            $sql = "SELECT status FROM grand_movedOn WHERE
                    user_id = '$hqp' AND 
                    effective_date LIKE '%{$this->getHqpEndDate()}%'";
            $data = DBFunctions::execSQL($sql);
            if(count($data)>0 && $data[0]['status'] != ""){
                 $status = $data[0]['status'];
            }
            return $status;
        }
    }
    
    // Essentially a copy of PersonCoursesReportItemSet
    function getCourseCount($term="", $unique=true, $exclude13Week=false){
        $data = array();
        $person = Person::newFromId($this->reportItem->personId);
        $component = '';
        if($term == ''){
            $courses = $person->getCourses($start, $end);
        }
        else{
            $courses = $person->getCourses();
        }
        $alreadyDone = array();
        $count = 0;
        if(is_array($courses)){
            foreach($courses as $course){
                if(($term == '' || strstr($term, $course->term_string) !== false) &&
                   ($component == '' || $component == $course->component)){
                    if($unique && isset($alreadyDone[$course->subject.$course->catalog])){
                        continue;
                    }
                    if($exclude13Week && strstr($course->term_string, "Spring") !== false && strstr($course->catalog, "A") !== false){
                        continue;
                    }
                    $count++;
                    $alreadyDone[$course->subject.$course->catalog] = $tuple;
                }
            }
        }
        return $count;
    }

    function getCourseTerm(){
        $course = Course::newFromId($this->reportItem->projectId);
        return $course->getTerm();
    }
    
    function getCourseStart(){
        $course = Course::newFromId($this->reportItem->projectId);
        return $course->getStartDate();
    }
    
    function getCourseEnd(){
        $course = Course::newFromId($this->reportItem->projectId);
        return $course->getEndDate();
    }

    function getCourseSubject(){
        $course = Course::newFromId($this->reportItem->projectId);
        return $course->subject;
    }

    function getCourseNumber(){
        $course = Course::newFromId($this->reportItem->projectId);
        return $course->catalog;
    }
    
    function getCourseTitle(){
        $course = Course::newFromId($this->reportItem->projectId);
        return $course->descr;
    }

    function getCourseComp(){
        $course = Course::newFromId($this->reportItem->projectId);
        return $course->component;
    }

    function getCourseSection(){
        $course = Course::newFromId($this->reportItem->projectId);
        return $course->sect;
    }
    
    function getCoursePercent(){
        $person = Person::newFromId($this->reportItem->personId);
        return $person->getCoursePercent($this->reportItem->projectId);
    }
    
    function getCourseCalendar(){
        $course = Course::newFromId($this->reportItem->projectId);
        return $course->getCalendarString();
    }
    
    function getCourseAllLectureEnroll(){
        $course = Course::newFromId($this->reportItem->projectId);
        $person = Person::newFromId($this->reportItem->personId);
        $data = DBFunctions::execSQL("SELECT SUM(c.`Tot Enrl`) as total
                                      FROM  `grand_courses` c, `grand_user_courses` uc
                                      WHERE c.id = uc.course_id
                                      AND uc.user_id = '{$person->getId()}'
                                      AND c.term_string = '{$course->term_string}'
                                      AND c.subject = '{$course->subject}'
                                      AND c.catalog = '{$course->catalog}'
                                      AND c.component = 'LEC'
                                      AND uc.percentage != '0'");
        if(@$data[0]['total'] == 0){
            $data = DBFunctions::execSQL("SELECT SUM(c.`Tot Enrl`) as total
                                          FROM  `grand_courses` c, `grand_user_courses` uc
                                          WHERE c.id = uc.course_id
                                          AND uc.user_id = '{$person->getId()}'
                                          AND c.term_string = '{$course->term_string}'
                                          AND c.subject = '{$course->subject}'
                                          AND c.catalog = '{$course->catalog}'
                                          AND (c.component = 'LAB' OR c.component = 'SEM')
                                          AND uc.percentage != '0'");
        }
        return @$data[0]['total'];
    }

    function getCourseEnroll(){
        $course = Course::newFromId($this->reportItem->projectId);
        return $course->totEnrl;
    }

    function getCourseEnrollPercent(){
        $course = Course::newFromId($this->reportItem->projectId);
        return ($course->totEnrl/max(1,$course->capEnrl))*100;
    }
    
    function getCourseEval($allSections=true){
        $person = Person::newFromId($this->reportItem->personId);
        $course = Course::newFromId($this->reportItem->projectId);
        $evals = $person->getCourseEval($course->getId(), $allSections);
        if(!is_array($evals)){
            return "";
        }
        
        usort($evals, function($a, $b){
            return $a['id'] - $b['id'];
        });
        
        $ret = "";
        foreach($evals as $key => $eval){
            $ret .= "<tr>";
            $qid = $eval['id'];
            $r1 = $eval['votes'][0];
            $r2 = $eval['votes'][1];
            $r3 = $eval['votes'][2];
            $r4 = $eval['votes'][3];
            $r5 = $eval['votes'][4];
            $qText = $eval['question'];
            $count = max(1, $r1 + $r2 + $r3 + $r4 + $r5);
            $avg = number_format(($r1*1+$r2*2+$r3*3+$r4*4+$r5*5)/$count, 2);
            $ret .= "
                <td align='right'>$qid</td>
                <td style='width:50%;'>$qText</td>
                <td align='right'>$r1</td>
                <td align='right'>$r2</td>
                <td align='right'>$r3</td>
                <td align='right'>$r4</td>
                <td align='right'>$r5</td>
                <td style='background: white;'>
                    <table padding='0' cellspacing='0' style='width:100%;'>
                        <tr>
                            <td style='background:#b33100; width: ".(($r1/$count)*100)."%; height:1em; padding:0;'></td>
                            <td style='background:#d55e00; width: ".(($r2/$count)*100)."%; height:1em; padding:0;'></td>
                            <td style='background:#0072b2; width: ".(($r3/$count)*100)."%; height:1em; padding:0;'></td>
                            <td style='background:#009e73; width: ".(($r4/$count)*100)."%; height:1em; padding:0;'></td>
                            <td style='background:#00662b; width: ".(($r5/$count)*100)."%; height:1em; padding:0;'></td>
                        </tr>
                    </table>
                </td>
            </tr>";
        }
        return str_replace("\n", "", $ret);
    }
    
    function getCourseEvalSummary($allSections=true){
        global $wgServer, $wgScriptPath, $DPI_CONSTANT;
        $dpi = (isset($_GET['generatePDF'])) ? $DPI_CONSTANT : 1;
        $person = Person::newFromId($this->reportItem->personId);
        $course = Course::newFromId($this->reportItem->projectId);
        $evals = $person->getCourseEval($course->getId(), $allSections);
        if(!is_array($evals) || count($evals) < 18){
            return "";
        }
        
        usort($evals, function($a, $b){
            return $a['id'] - $b['id'];
        });
        
        $groups = array('Design' => array(),
                        'Utility of Course<br />Resources' => array(),
                        'Graded Work' => array(),
                        'Course Delivery' => array(),
                        'Instructional Approach' => array(),
                        'Class Climate' => array());
        
        $count = 0;
        foreach($evals as $key => $eval){
            $index = null;
            if($key+1 <= 3){
                $index = 'Design';
            }
            else if($key+1 <= 6){
                $index = 'Utility of Course<br />Resources';
            }
            else if($key+1 <= 9){
                $index = 'Graded Work';
            }
            else if($key+1 <= 12){
                $index = 'Course Delivery';
            }
            else if($key+1 <= 15){
                $index = 'Instructional Approach';
            }
            else if($key+1 <= 18){
                $index = 'Class Climate';
            }
            if($index != null){
                $groups[$index][0] += $eval['votes'][0];
                $groups[$index][1] += $eval['votes'][1];
                $groups[$index][2] += $eval['votes'][2];
                $groups[$index][3] += $eval['votes'][3];
                $groups[$index][4] += $eval['votes'][4];
                if($key == 0){
                    $count = array_sum($eval['votes']);
                }
            }
        }
        
        $ret = "<table class='wikitable' style='width: 100%;' frame='box' rules='all'>
                    <tr>
                        <th style='width:20%;' colspan='4'>SPOT Domain / Q#</th>
                        <th style='width:16%;'>Strongly Disagree (SD)</th>
                        <th style='width:16%;'>Disagree (D)</th>
                        <th style='width:16%;'>Neither Agree Nor Disagree (N)</th>
                        <th style='width:16%;'>Agree (A)</th>
                        <th style='width:16%;'>Strongly Agree (SA)</th>
                    </tr>";
        $max = 0;
        $key = 0;
        foreach($groups as $index => $votes){
            $max = max($max, $votes[0], $votes[1], $votes[2], $votes[3], $votes[4]);
            $ret .= "<tr>
                        <td align='center' style='width:17%; white-space:nowrap;'>".str_replace("<br />", " ", $index)."</td>
                        <td align='center' style='width: 1%;'>".($key*3+1)."</td>
                        <td align='center' style='width: 1%;'>".($key*3+2)."</td>
                        <td align='center' style='width: 1%;'>".($key*3+3)."</td>
                        <td align='center'>{$votes[0]}</td>
                        <td align='center'>{$votes[1]}</td>
                        <td align='center'>{$votes[2]}</td>
                        <td align='center'>{$votes[3]}</td>
                        <td align='center'>{$votes[4]}</td>
                    </tr>";
            $key++;
        }
        
        while($max % 4 != 0){
            $max++;
        }
        
        $ret .= "</table><br /><br />";
        
        $data = file_get_contents("skins/chartbg.png");
        $base64 = 'data:image/png;base64,' . base64_encode($data);
        
        $border = 1*$dpi;
        $height = 10;
        $calcHeight = (!isset($_GET['generatePDF'])) ? "height: calc(10em + 4px);" : "";
        
        $enrolled = $this->getCourseEvalEnrolled();
        $responses = $this->getCourseEvalResponses();
        
        if(isset($_GET['generatePDF']) && isset($_GET['preview'])){
            // Preview
            $top = 17.6;
            $line = 3.3;
            $display = "block";
        }
        else if(!isset($_GET['generatePDF'])){
            // Normal view
            $top = 21;
            $line = 3.3;
            $display = "inline-block";
        }
        else {
            // PDF
            $top = 18;
            $line = 1.65;
            $display = "block";
        }
        
        $ret .= "<style>
                    table.chart td {
                        background-color: transparent !important;
                    }
                    
                    table.chart {
                        color: #333333;
                    }
                 </style>
                 <div style='width: 100%; text-align: center; font-weight: bold; color: #333333; margin-bottom: 0.25em;'>
                    SPOT Frequency Distribution<br />
                    Responses: {$responses} of {$enrolled} (".number_format($responses/max(1, $enrolled)*100, 1)."%)
                 </div>
                 <img src='$base64' style='width: 97.5%; margin-left: 2.5%; height: {$height}em; border-width: {$border}px {$border}px 0 {$border}px; border-style: solid; border-color: #e0e0e0;' />
                 <table class='chart' cellspacing='0' cellpadding='0' style='margin-top: -{$height}em; width: 100%; height: {$height}em; border-spacing: 0; border-collapse: separate;'>
                    <tr>
                        <td valign='top' style='width:5%; {$calcHeight}'></td>";
        foreach($groups as $index => $votes){
            foreach($votes as $i => $vote){
                $color = "";
                switch($i){
                    case 0: $color = "#e84236"; break;
                    case 1: $color = "#fe6d00"; break;
                    case 2: $color = "#f9c001"; break;
                    case 3: $color = "#01c0bb"; break;
                    case 4: $color = "#2fa954"; break;
                }
                $bartop = 1*$dpi;
                $marginTop = ((($vote/$max)*$height) <= 1) ? "-1.25em" : 0;
                $ret .= "<td valign='bottom' style='height: {$height}em; width: 2.5%;'>
                            <div style='position: relative; display: {$display}; bottom: 0; background: $color; width: 100%; height: ".(($vote/$max)*$height)."em;'>
                                <div style='position: absolute; top: {$bartop}px; margin-top: {$marginTop}; width: 100%; line-height: 1em; font-size: 0.75em; color: black; text-align: center;'>{$vote}</div>
                            </div>
                         </td>";
            }
            $ret .= "<td style='width: 2.5%;'></td>";
        }
        $ret .= "   </tr>
                    <tr style='font-size: 0.75em;'>
                        <td></td>";
        foreach($groups as $index => $votes){
            $ret .= "<td align='center'>SD</td>
                     <td align='center'>D</td>
                     <td align='center'>N</td>
                     <td align='center'>A</td>
                     <td align='center'>SA</td>
                     <td align='center'></td>";
        }
        $ret .= "   </tr>
                    <tr style='font-size: 0.75em; font-weight: bold;'>
                        <td></td>";
        foreach($groups as $index => $votes){
            $ret .= "<td colspan='5' valign='top' align='center'>{$index}</td>
                     <td></td>";
        }
        
        $ret .= "   </tr>
                    <tr>
                        <td valign='top'>
                            <div style='width: 35%; text-align: right; margin-top: -{$top}em; height: 10em; position:relative; font-size: 0.75em; font-weight: bold;'>
                                 <div style='position: absolute; top: ".($line*0)."; right:0;'>$max</div>
                                 <div style='position: absolute; top: ".($line*1)."em; right:0;'>".($max*0.75)."</div>
                                 <div style='position: absolute; top: ".($line*2)."em; right:0;'>".($max*0.5)."</div>
                                 <div style='position: absolute; top: ".($line*3)."em; right:0;'>".($max*0.25)."</div>
                                 <div style='position: absolute; top: ".($line*4)."em; right:0;'>0</div>
                             </div>
                         </td>
                    </tr>
                 </table>
                 ";
        return $ret;
    }
    
    function getCourseEvalN(){
        $person = Person::newFromId($this->reportItem->personId);
        $course = Course::newFromId($this->reportItem->projectId);
        $evals = $person->getCourseEval($course->getId(), false);
        if(!is_array($evals)){
            return "";
        }
        
        usort($evals, function($a, $b){
            return $a['id'] - $b['id'];
        });
        
        $ret = "";
        foreach($evals as $key => $eval){
            $r1 = $eval['votes'][0];
            $r2 = $eval['votes'][1];
            $r3 = $eval['votes'][2];
            $r4 = $eval['votes'][3];
            $r5 = $eval['votes'][4];
            return $r1 + $r2 + $r3 + $r4 + $r5;
        }
        return 1;
    }
    
    function getCourseEvalEnrolled(){
        $person = Person::newFromId($this->reportItem->personId);
        $course = Course::newFromId($this->reportItem->projectId);
        $evals = $person->getCourseEval($course->getId(), false);
        if(!is_array($evals)){
            return "";
        }
                    
        $ret = 0;
        foreach($evals as $key => $eval){
            $ret = max($ret, $eval['enrolled']);
        }
        return $ret;
    }
    
    function getCourseEvalResponses(){
        $person = Person::newFromId($this->reportItem->personId);
        $course = Course::newFromId($this->reportItem->projectId);
        $evals = $person->getCourseEval($course->getId(), false);
        if(!is_array($evals)){
            return "";
        }
                    
        $ret = 0;
        foreach($evals as $key => $eval){
            $ret = max($ret, $eval['responses']);
        }
        return $ret;
    }
    
    function getAverageCourseEvalByTerm($term, $q){
        $person = Person::newFromId($this->reportItem->personId);
        $courses = $person->getCoursesDuring(SOT, EOT);
        $avgs = array();
        foreach($courses as $course){
            if($course->component == "LEC" && strstr($term, $course->term_string) !== false){
                $evals = $person->getCourseEval($course->getId());
                $ret = array();
                foreach($evals as $key => $eval){
                    list($R, $G, $B) = $this->evalColor($eval);
                    if("Q$q" == $key){
                        $avgs[] = $eval;
                    }
                }
            }
        }
        $avg = number_format(array_sum($avgs)/max(1, count($avgs)), 2);
        list($R, $G, $B) = $this->evalColor($avg);
        return "<span style='padding-left: 0.25em; padding-right: 0.25em; display: inline; background: rgb($R,$G,$B);'>{$avg}</span>";
    }
    
    function getGrantId(){
        $grant = Grant::newFromId($this->reportItem->productId);
        return $grant->getId();
    }
 
    function getGrantTitle(){
        $grant = Grant::newFromId($this->reportItem->productId);
        if($grant->getScientificTitle() != ""){
            return $grant->getScientificTitle();
        }
        return $grant->getTitle();
    }
    
    function getGrantScientificTitle(){
        $grant = Grant::newFromId($this->reportItem->productId);
        return $grant->getScientificTitle();
    }
    
    function getGrantDescription(){
        $grant = Grant::newFromId($this->reportItem->productId);
        $description = $grant->getDescription();
        if(trim($description) == ""){
            $description = $grant->getTitle();
        }
        return $description;
    }
    
    function getGrantTitleBoth(){
        $grant = Grant::newFromId($this->reportItem->productId);
        $description = $grant->getDescription();
        $title = ($grant->getScientificTitle() != "") ? $grant->getScientificTitle() : $grant->getTitle();
        if(trim($description) != ""){
            return $title."<br />".$description;
        }
        return $title;
    }
    
    function getGrantProjectId(){
        $grant = Grant::newFromId($this->reportItem->productId);
        return $grant->getProjectId();
    }
    
    function getGrantSponsor(){
        $grant = Grant::newFromId($this->reportItem->productId);
        return $grant->getSponsor();
    }
    
    function getGrantStartDate(){
        $grant = Grant::newFromId($this->reportItem->productId);
        return time2date($grant->getStartDate(), "Y-m-d");
    }
    
    function getGrantEndDate(){
        $grant = Grant::newFromId($this->reportItem->productId);
        return time2date($grant->getEndDate(), "Y-m-d");
    }
    
    function getGrantTotal(){
        $grant = Grant::newFromId($this->reportItem->productId);
        return number_format($grant->getTotal());
    }
    
    function getGrantMyPortion(){
        $grant = Grant::newFromId($this->reportItem->productId);
        return number_format($grant->getMyPortion());
    }
    
    function getGrantAverage(){
        $grant = Grant::newFromId($this->reportItem->productId);
        $average = $grant->getAverage();
        $adjusted = $grant->getAdjustedAmount();
        return ($adjusted > 0) ? @number_format($adjusted, 2) : number_format($grant->getAverage(), 2);
    }
    
    function isGrantPI(){
        $grant = Grant::newFromId($this->reportItem->productId);
        $pi = $grant->getPI();
        if($pi instanceof Person && $pi->getNameForForms() == $this->reportItem->getReport()->person->getNameForForms()){
            return "Yes";
        }
        return "No";
    }
    
    function getGrantPeople(){
        $grant = Grant::newFromId($this->reportItem->productId);
        $pi = $grant->getPI();
        $externalPI = $grant->getExternalPI();
        $copis = $grant->getCoPI();
        $people = array();
        if($pi instanceof Person && $pi->getNameForForms() != "" && $pi->getNameForForms() != $this->reportItem->getReport()->person->getNameForForms()){
            $people[] = $pi->getNameForForms();
        }
        if(trim($externalPI) != ""){
            $people[] = trim($externalPI);
        }
        foreach($copis as $copi){
            if(is_string($copi)){
                $people[] = $copi;
            }
            else if($copi->getNameForForms() != $this->reportItem->getReport()->person->getNameForForms()){
                $people[] = "<span style='background: #dfdfdf;' class='citation_author faculty_author'>{$copi->getNameForForms()}</span>";
            }
        }
        return implode("; ", $people);
    }
    
    function getOutputId(){
        return $this->reportItem->extra." ".$this->reportItem->productId;
    }
    
    function getOutputTitle(){
        $class = $this->reportItem->extra;
        $obj = $class::newFromId($this->reportItem->productId);
        return $obj->getTitle();
    }
    
    function getReportHasStarted(){
        $report = $this->reportItem->getReport();
        if($report->hasStarted()){
            return "<span style='font-weight:bold;color:#008800;'>Yes</span>";
        }
        return "<span>No</span>";
    }
    
    function getMyId(){
        $person = Person::newFromWgUser();
        return $person->getId();
    }
    
    function getMyName(){
        $person = Person::newFromWgUser();
        return $person->getNameForForms();
    }
    
    function getMyFirstName(){
        $person = Person::newFromWgUser();
        return $person->getFirstName();
    }
    
    function getMyLastName(){
        $person = Person::newFromWgUser();
        return $person->getLastName();
    }
    
    function getMyDept(){
        $person = Person::newFromWgUser();
        $fecInfo = $person->getFecPersonalInfo();
        $departments = @array_keys($person->departments);
        if(count($departments) > 0){
            return $departments[0];
        }
        else{
            $university = $person->getUniversity();
            return $university['department'];
        }
    }
    
    function getMyRoles($delim=", "){
        $person = Person::newFromWgUser();
        $roles = $person->getRoles();
        $roleNames = array();
        foreach($roles as $role){
            $roleNames[$role->getRole()] = $role->getRole();
        }
        return implode($delim, $roleNames);
    }
    
    function getMyRolesDuring(){
        $person = Person::newFromWgUser();
        $roles = $person->getRolesDuring($this->reportItem->getReport()->startYear.CYCLE_START_MONTH, $this->reportItem->getReport()->year.CYCLE_END_MONTH);
        $roleNames = array();
        foreach($roles as $role){
            $roleNames[$role->getRole()] = $role->getRole();
        }
        return implode(", ", $roleNames);
    }
    
    function getMySubRoles($delimiter=", "){
        global $config;
        $person = Person::newFromWgUser();
        $roles = array();
        foreach(@$person->getSubRoles() as $subRole){
            $roles[] = $config->getValue('subRoles', $subRole);
        }
        return implode($delimiter, $roles);
    }
    
    function getUserUrl(){
        $person = Person::newFromId($this->reportItem->personId);
        return $person->getUrl();
    }
    
    function getUserEmail(){
        $person = Person::newFromId($this->reportItem->personId);
        return $person->getEmail();
    }
    
    function getUserPhone(){
        $person = Person::newFromId($this->reportItem->personId);
        return $person->getPhoneNumber();
    }
    
    function getUserProfile(){
        $person = Person::newFromId($this->reportItem->personId);
        return $person->getProfile();
    }
    
    function getParentId(){
        return $this->reportItem->getParent()->personId;
    }
    
    function getParentName(){
        $person = Person::newFromId($this->getParentId());
        return $person->getNameForForms();
    }
    
    function getParentUni(){
        $person = Person::newFromId($this->getParentId());
        return $person->getUni();
    }
    
    function getUserName(){
        $person = Person::newFromId($this->reportItem->personId);
        return $person->getNameForForms();
    }
    
    function getUserReversedName(){
        $person = Person::newFromId($this->reportItem->personId);
        return $person->getReversedName();
    }
    
    function getUserLastName(){
        $person = Person::newFromId($this->reportItem->personId);
        return $person->getLastName();
    }
    
    function getUserFirstName(){
        $person = Person::newFromId($this->reportItem->personId);
        return $person->getFirstName();
    }
    
    function getUserId(){
        return $this->reportItem->personId;
    }
    
    function getUserPhoto(){
        $person = Person::newFromId($this->reportItem->personId);
        return $person->getPhoto();
    }
    
    function getUserWebsite(){
        $person = Person::newFromId($this->reportItem->personId);
        return $person->getWebsite();
    }
    
    function getUserLdap(){
        $person = Person::newFromId($this->reportItem->personId);
        return $person->getLdap();
    }
    
    function getUserRoles(){
        $person = Person::newFromId($this->reportItem->personId);
        $roles = $person->getRoles();
        $roleNames = array();
        foreach($roles as $role){
            $roleNames[$role->getRole()] = $role->getRole();
        }
        return implode(", ", $roleNames);
    }
    
    function getUserFullRoles(){
        $person = Person::newFromId($this->reportItem->personId);
        $roles = $this->getUserRoles();
        return $roles;
    }
    
    function getUserSubRoles($delimiter=", "){
        global $config;
        $person = Person::newFromId($this->reportItem->personId);
        $roles = array();
        foreach(@$person->getSubRoles() as $subRole){
            $roles[] = $config->getValue('subRoles', $subRole);
        }
        return implode($delimiter, $roles);
    }
    
    function getUserUniStart(){
        $person = Person::newFromId($this->reportItem->personId);
        $university = $person->getUniversity();
        $date = new DateTime($university['start']);
        return date_format($date, 'F Y');
    }
    
    function getUserUniEnd(){
        $person = Person::newFromId($this->reportItem->personId);
        $university = $person->getUniversity();
        if($university['date'] == ZOTT){
            return "";
        }
        $date = new DateTime($university['date']);
        return date_format($date, 'F Y');
    }
    
    function getUserLevel($start=null, $end=null, $delim=", "){
        $person = Person::newFromId($this->reportItem->personId);
        if($start != null && $end != null){
            $levels = array();
            foreach($person->getUniversitiesDuring($start, $end) as $university){
                $levels[] = $university['position'];
            }
            return implode($delim, $levels);
        }
        else{
            $university = $person->getUniversity();
            return $university['position'];
        }
    }
    
    function getUserTitle($date=null){
        if($date == null){
            $date = CYCLE_END;
        }
        $person = Person::newFromId($this->reportItem->personId);
        $person->getFecPersonalInfo();
        if($person->isRoleOn("ATS", $date) && $person->isATSEC1($date)){
            return "Assistant Lecturer";
        }
        else if($person->isRoleOn("ATS", $date) && $person->isATSEC2($date)){
            return "Associate Lecturer";
        }
        else if($person->isRoleOn("ATS", $date) && $person->isATSEC3($date)){
            return "Full Lecturer";
        }
        else if($person->isAssistantProfessor($date)){
            return "Assistant Professor";
        }
        else if($person->isAssociateProfessor($date)){
            return "Associate Professor";
        }
        else if($person->isProfessor($date)){
            return "Full Professor";
        }
        else if($person->isFSO2($date)){
            return "FSO II";
        }
        else if($person->isFSO3($date)){
            return "FSO III";
        }
        else if($person->isFSO4($date)){
            return "FSO IV";
        }
        return $this->getUserlevel();
    }
    
    function getUserDept(){
        $person = Person::newFromId($this->reportItem->personId);
        $fecInfo = $person->getFecPersonalInfo();
        $departments = @array_keys($person->departments);
        if(count($departments) > 0){
            return $departments[0];
        }
        else{
            $university = $person->getUniversity();
            return $university['department'];
        }
    }
    
    function getUserDepts(){
        $person = Person::newFromId($this->reportItem->personId);
        $fecInfo = $person->getFecPersonalInfo();
        $departments = @array_keys($person->departments);
        $percents = @array_values($person->departments);
        
        switch(count($departments)){
            case 0:
                // Fallback
                $html = $this->getUserDept();
                break;
            case 1:
                $html = "{$departments[0]}";
                break;
            case 2:
                $html = "{$departments[0]} ({$percents[0]}%), {$departments[1]} ({$percents[1]}%)";
                break;
        }
        return $html;        
    }
    
    function getUserUni(){
        $person = Person::newFromId($this->reportItem->personId);
        $university = $person->getUniversity();
        return $university['university'];
    }
    
    function getUserResearchArea(){
        $person = Person::newFromId($this->reportItem->personId);
        $university = $person->getUniversity();
        return $university['research_area'];
    }
    
    function getUserFEC(){
        $person = Person::newFromId($this->reportItem->personId);
        return $person->getFECType($this->reportItem->getReport()->year.CYCLE_END_MONTH);
    }
    
    function getUserCaseNumber(){
        $person = Person::newFromId($this->reportItem->personId);
        return $person->getCaseNumber($this->reportItem->getReport()->year);
    }
    
    function getUserKeywords(){
        $person = Person::newFromId($this->reportItem->personId);
        return $person->getKeywords(", ");
    }
    
    function getUserSupervisors($excludeMe=false, $startDate="", $endDate=""){
        $supervisors = array();
        $person = Person::newFromId($this->reportItem->personId);
        $startDate = ($startDate == "") ? $this->reportItem->getReport()->startYear.CYCLE_START_MONTH : $startDate;
        $endDate = ($endDate == "") ? $this->reportItem->getReport()->year.CYCLE_END_MONTH : $endDate;
        $supervisors = $person->getSupervisorsDuring($startDate, $endDate);
        $sups = array();
        foreach($supervisors as $supervisor){
            if($excludeMe && $supervisor->getNameForForms() == $this->reportItem->getReport()->person->getNameForForms()){
                continue;
            }
            $sups[] = $supervisor->getNameForForms();
        }
        return implode("; ", $sups);
    }
    
    function getUserCoSupervisors(){
        $me = $this->reportItem->getReport()->person;
        $person = Person::newFromId($this->reportItem->personId);
        $startDate = $this->reportItem->getReport()->startYear.CYCLE_START_MONTH;
        $endDate = $this->reportItem->getReport()->year.CYCLE_END_MONTH;
        
        $startDate = ($this->getExtra('start_date') != "" && $this->getExtra('start_date') != ZOT) ? max($this->getExtra('start_date'), $startDate) : $startDate;
        $endDate   = ($this->getExtra('end_date')   != "" && $this->getExtra('end_date')   != ZOT) ? min($this->getExtra('end_date'),   $endDate)   : $endDate;
        if($me->isRelatedToDuring($person, CO_SUPERVISES, $startDate, $endDate)){
            // Only show if the person is a co-supervisor
            return $this->getUserSupervisors(true, $startDate, $endDate);
        }
        return "";
    }
    
    function getUserServiceCount($start_date, $end_date, $type){
        $person = Person::newFromId($this->reportItem->personId);
        $products = $person->getPapersAuthored("Activity", $start_date, $end_date, false, true, true);
        $count = 0;
        foreach($products as $product){
            if($product->getType() == $type || $type == ""){
                $count++;
            }
        }
        return $count;
    }

    function getUserPublicationCount($start_date, $end_date, $case='Publication'){
        $year = substr($start_date, 0, 4);
        $person = Person::newFromId($this->reportItem->personId);
        $category = "";
        switch($case){
            case "Any Journal":
                $category = "Publication";
                $type = "Journal Paper";
                $histories = array();
                break;
            case "Not Refereed":
                $category = "Publication";
                $type = "*";
                $histories = array();
                break;
            case "Journal":
                $case = "Publication";
                $category = "Publication";
                $type = "Journal Paper";
                $histories = $person->getProductHistories($year, "Refereed");
                break;
            case "Conference":
                $case = "Publication";
                $category = "Publication";
                $type = "Conference Paper";
                $histories = $person->getProductHistories($year, "Refereed");
                break;
            case "Publication":
                $category = "Publication";
                $type = "*";
                $histories = $person->getProductHistories($year, "Refereed");
                break;
            case "Book":
                $category = "Publication";
                $type = "Book";
                $histories = $person->getProductHistories($year, "Book");
                break;
            case "Patent":
                $category = "Patent/Spin-Off";
                $type = "Patent";
                $histories = $person->getProductHistories($year, "Patent");
                break;
            case "Report of Invention":
                $category = "Patent/Spin-Off";
                $type = "Report of Invention";
                $histories = $person->getProductHistories($year, "Report of Invention");
                break;
            case "AllPublication":
                $category = "Publication";
                $type = "*";
                $histories = array();
                break;
            case "Award":
                $category = "Award";
                $type = "*";
                $histories = array();
                break;
            case "Presentation":
                $category = "Presentation";
                $type = "*";
                $histories = array();
                break;
            case "Patent/Spin-Off":
                $category = "Patent/Spin-Off";
                $type = "*";
                $histories = array();
                break;
            default:
                $category = $case;
                $type = "*";
                $histories = array();
                break;
        }
        
        if(count($histories) > 0){
            return $histories[0]->getValue();
        }
        $products = $person->getPapersAuthored($category, $start_date, $end_date, false, true, true);
        $count = 0;
        $types = explode("|", $type);
        foreach($products as $product){
            if(in_array($product->getType(), $types) || implode($types) == "*"){
                $reportedYear = $product->getReportedForPerson($this->reportItem->personId);
                if($reportedYear == "" && $product->getType() != "Patent"){
                    // Not reported yet, but do some checks to make sure we don't count it twice
                    if($product->getDate() > $end_date){
                        // Found one, set the reported year to next year as long as next year isn't greater than the current reporting year
                        $reportedYear = min($year+1, $this->reportItem->getReport()->year-1);
                    }
                }
                if($reportedYear == "" || $reportedYear == $year){
                    if($case == "Publication"){
                        if($product->getData('peer_reviewed') == "Yes"){
                            $count++;
                        }
                    }
                    else if($case == "Not Refereed"){
                        if($product->getData('peer_reviewed') == "No" || $product->getData('peer_reviewed') == ""){
                            $count++;
                        }
                    }
                    else{
                        $count++;
                    }
                }
            }
        }
        return $count;
    }

    function getUserLifetimePublicationCount($type='all'){
        $person = Person::newFromId($this->reportItem->personId);
        $phdYear = min(2006, max($person->getProductHistoryEarliestYear(), $this->getUserPhdYear()-10)); // Start at the phd year -10 so there is some flexibility, but also no later than 2006
        $year = $this->reportItem->getReport()->year;
        $count = 0;
        for($y=$phdYear; $y <= $year; $y++){
            switch($type){
                default:
                case "Publication":
                    $previousCounts = $person->getProductHistories($y-1, "Previous Refereed");
                    break;
                case "Book":
                    $previousCounts = $person->getProductHistories($y-1, "Previous Book");
                    break;
                case "Patent":
                    $previousCounts = $person->getProductHistories($y-1, "Previous Patent");
                    break;
            }
            if(count($previousCounts) > 0){
                // Reset the count
                $count = $previousCounts[0]->getValue();
            }
            $count += $this->getUserPublicationCount(($y-1).CYCLE_START_MONTH,($y).CYCLE_END_MONTH,$type);
        }
        return $count;
    }
    
    function isAllowedToViewRecommendation($userId){
        $deptPeople = new DepartmentPeopleReportItemSet();
        $deptPeople->setParent($this->reportItem->getSection());
        $deptPeople->setAttribute('start', '{$last_year}-07-01');
        $deptPeople->setAttribute('end', '{$this_year}-07-01');
        $deptPeople->setAttribute('university', 'University of Alberta');
        $deptPeople->setAttribute('department', '{$my_dept}');
        $deptPeople->setAttribute('excludeMe', 'true');
        
        $atsecPeople = new DepartmentPeopleReportItemSet();
        $atsecPeople->setParent($this->reportItem->getSection());
        $atsecPeople->setAttribute('atsec', 'true');
        $atsecPeople->setAttribute('start', '{$last_year}-07-01');
        $atsecPeople->setAttribute('end', '{$this_year}-07-01');
        $atsecPeople->setAttribute('university', 'University of Alberta');
        $atsecPeople->setAttribute('department', '{$my_dept}');
        $atsecPeople->setAttribute('excludeMe', 'true');
        
        $deanPeople = new DeansPeopleReportItemset();
        $deanPeople->setParent($this->reportItem->getSection());
        $deanPeople->setAttribute('start', '{$last_year}-07-01');
        $deanPeople->setAttribute('end', '{$this_year}-07-01');
        
        $data = array_merge($deptPeople->getData(), $atsecPeople->getData(), $deanPeople->getData());
        foreach($data as $tuple){
            if($tuple['person_id'] == $userId){
                return true;
            }
        }
        return false;
    }

    function getPersonVar($var){
        $user = Person::newFromId($this->reportItem->personId);
        if(isset($user->{$var})){
            return $user->{$var};
        }
        return "";
    }

    function getChairId(){
        $user = Person::newFromId($this->reportItem->personId);
        $people = Person::getAllPeople(CHAIR);
        foreach($people as $person){
            if($person->getDepartment() == $user->getDepartment()){
                return $person->getId();
            }
        }
        return 0;
    }
    
    function getProductId(){
        $product = Paper::newFromId($this->reportItem->productId);
        return $product->getId();
    }
    
    function getProductType(){
        $product = Paper::newFromId($this->reportItem->productId);
        return str_replace("Misc: ", "", str_replace("Other: ", "", $product->getType()));
    }
    
    function getProductTitle(){
        $product = Paper::newFromId($this->reportItem->productId);
        return $product->getTitle();
    }
    
    function getProductStatus(){
        $product = Paper::newFromId($this->reportItem->productId);
        return $product->getStatus();
    }
    
    function getProductAuthors(){
        $product = Paper::newFromId($this->reportItem->productId);
        $authors = $product->getAuthors();
        $array = array();
        foreach($authors as $author){
            $array[] = $author->getReversedName();
        }
        return implode("; ", $array);
    }
    
    function getProductContributors(){
        $product = Paper::newFromId($this->reportItem->productId);
        $authors = $product->getContributors();
        $array = array();
        foreach($authors as $author){
            $array[] = $author->getReversedName();
        }
        return implode("; ", $array);
    }
    
    function getProductDescription(){
        $product = Paper::newFromId($this->reportItem->productId);
        return $product->getDescription();
    }
    
    function getProductUrl(){
        $product = Paper::newFromId($this->reportItem->productId);
        return $product->getUrl();
    }
   
    function getProductCitation(){
        $product = Paper::newFromId($this->reportItem->productId);
        if($this->reportItem->personId > 0){
            return $product->getCitation(true, true, false, false, $this->reportItem->personId);
        }
        return $product->getCitation(true, true, false, false, true);
    }
    
    function getProductQACitation(){
        $product = Paper::newFromId($this->reportItem->productId);
        return $product->getCitation(true, false, false, false, true);
    }
    
    function getProductScope(){
        $product = Paper::newFromId($this->reportItem->productId);
        return $product->getData('scope');
    }
    
    function getProductData($field){
        $product = Paper::newFromId($this->reportItem->productId);
        return $product->getData($field);
    }

    function getPresentationTitle(){
        $product = Paper::newFromId($this->reportItem->productId);
        return $product->getTitle();
    }
    
    function getPresentationType(){
        $product = Paper::newFromId($this->reportItem->productId);
        return str_replace("Misc: ", "", str_replace("Other: ", "", $product->getType()));
    }

    function getPresentationInvited(){
        $product = Paper::newFromId($this->reportItem->productId);
        $status = $product->getStatus();
        if($status == "Invited"){
            return "Yes";
        }
        return "No";
    }
      
    function getPresentationOrganization(){
        $product = Paper::newFromId($this->reportItem->productId);
        $product = $product->getData();
        return @$product['organizing_body'];
    }
    
    function getPresentationCountry(){
        $product = Paper::newFromId($this->reportItem->productId);
        $product = $product->getData();
        return (@$product['location'] != "") ? @$product['location'] : @$product['city'];
    }
    
    function getPresentationLength(){
        $product = Paper::newFromId($this->reportItem->productId);
        $product = $product->getData();
        return @$product['length'];
    }
    
    function getProductDate(){
        $product = Paper::newFromId($this->reportItem->productId);
        return @$product->getDate();
    }
    
    function getAwardScope(){
        $product = Paper::newFromId($this->reportItem->productId);
        $product = $product->getData();
        return @$product['scope'];
    }
    
    function getAwardedBy(){
        $product = Paper::newFromId($this->reportItem->productId);
        $product = $product->getData();
        return @$product['awarded_by'];
    }
    
    function getAwardYearRange(){
        $product = Paper::newFromId($this->reportItem->productId);
        $startYear = substr($product->getData('start_date'), 0, 4);
        $endYear = substr($product->getData('end_date'), 0, 4);
        if($startYear == $endYear){
            return $endYear;        
        }
        else if($startYear == "0000"){
            return $endYear;
        }
        else if($endYear == "0000"){
            return "{$startYear} - Present";
        }
        else{
            return "{$startYear} - {$endYear}";
        }
    }
    
    function getProductAcceptanceYear(){
        $product = Paper::newFromId($this->reportItem->productId);
        return @$product->getAcceptanceYear();
    }
    
    function getProductYear(){
        $product = Paper::newFromId($this->reportItem->productId);
        if($product->getData('yearly') == 1){
            $productStart = substr($product->getData('start_date'), 0, 4);
            $productEnd = substr($product->getData('end_date'), 0, 4);
            $reportStart = $this->reportItem->getReport()->startYear;
            $reportEnd = $this->reportItem->getReport()->year;
            
            $start = ($productStart > $reportStart && $productStart != "0000") ? $productStart : $reportStart;
            $end = ($productEnd < $reportEnd && $productEnd != "0000") ? $productEnd : $reportEnd;
            return "$start-$end";
        }
        return @$product->getYear();
    }
    
    function getProductYearRange(){
        $product = Paper::newFromId($this->reportItem->productId);
        $startYear = $product->getAcceptanceYear();
        $endYear = $product->getYear();
        if($startYear == $endYear){
            return $endYear;        
        }
        else if($startYear == "0000"){
            return $endYear;
        }
        else if($endYear == "0000"){
            return "{$startYear} - Present";
        }
        else{
            return "{$startYear} - {$endYear}";
        }
    }
    
    function getProductDateRange(){
        $product = Paper::newFromId($this->reportItem->productId);
        $startYear = $product->getAcceptanceYear();
        $endYear = $product->getYear();
        $startDate = $product->getAcceptanceDate();
        $endDate = $product->getDate();
        if($startDate == $endDate){
            return $endDate;
        }
        else if($startYear == "0000"){
            return $endDate;
        }
        else if($endYear == "0000"){
            return "{$startDate} - Present";
        }
        else{
            return "{$startDate} - {$endDate}";
        }
    }

    function getWgUserId(){
        global $wgUser;
        return $wgUser->getId();
    }
    
    function getWgServer(){
        global $wgServer;
        return $wgServer;
    }
    
    function getWgScriptPath(){
        global $wgScriptPath;
        return $wgScriptPath;
    }

    function getGet($var1, $default=""){
        if(isset($_GET[$var1])){
            return $_GET[$var1];
        }
        return $default;
    }
    
    function getNetworkName(){
        global $config;
        return $config->getValue('networkName');
    }
    
    function getId(){
        $personId = $this->reportItem->personId;
        $projectId = $this->reportItem->projectId;
        $productId = $this->reportItem->productId;
        
        if($personId != 0){
            return $personId;
        }
        else if($projectId != 0){
            return $projectId;
        }
        else if($productId != 0){
            return $productId;
        }
    }
    
    function strtotime($date){
        $date = str_replace("&#44;", ",", $date);
        return strtotime($date);
    }
    
    function date_format($time, $format='F j, Y'){
        $format = str_replace("#COMMA", ",", $format);
        return date($format, $time);
    }
    
    function time2date($time, $format='F j, Y'){
        $format = str_replace("#COMMA", ",", $format);
        return time2date($time, $format);
    }
    
    function getName(){
        $personId = $this->reportItem->personId;
        $productId = $this->reportItem->productId;
        
        if($personId != 0){
            $person = Person::newFromId($personId);
            return $person->getNameForForms();
        }
        else if($productId != 0){
            $product = Product::newFromId($productId);
            return $product->getTitle();
        }
    }
    
    function getI(){
        return $this->getIndex() - 1;
    }
    
    function getIndex(){
        $personId = $this->reportItem->personId;
        $projectId = $this->reportItem->projectId;
        $productId = $this->reportItem->productId;
        $milestoneId = $this->reportItem->milestoneId;
        $extra = $this->reportItem->extra;
        $set = $this->reportItem->getSet();
        while($set instanceof ToggleHeaderReportItemSet){
            $set = $set->getSet();
        }
        $i = 1;
        foreach($set->getData() as $item){
            if($item['milestone_id'] == $milestoneId &&
               $item['project_id'] == $projectId &&
               $item['person_id'] == $personId &&
               $item['product_id'] == $productId &&
               md5(serialize($item['extra'])) == md5(serialize($extra))){
                return $i;
            }
            $i++;
        }
        return 0;
    }
    
    function getValue(){
        $default = $this->reportItem->getAttr('default', '');
		if($default != ''){
		    return $default;
		}
		else{
		    return $this->reportItem->getBlobValue();
		}
    }
    
    function getExtraIndex(){
        $set = $this->reportItem->getSet();
        while(!($set instanceof ArrayReportItemSet)){
            $set = $set->getParent();
            if($set instanceof AbstractReport){
                return 0;
            }
        }
        foreach($set->getData() as $index => $item){
            if($item['extra'] == $this->reportItem->extra){
                return $index;
            }
        }
        return 0;
    }
    
    function getNProducts($startDate = false, $endDate = false, $category="all", $type="all", $data="", $includeHQP="true", $peerReviewed="false"){
        $products = array();
        $includeHQP = (strtolower($includeHQP) == "true" || $includeHQP === true);
        $peerReviewed = (strtolower($peerReviewed) == "true" || $peerReviewed === true);
        if($this->reportItem->personId != 0){
            // Person Products
            $person = Person::newFromId($this->reportItem->personId);
            $products = $person->getPapersAuthored($category, $startDate, $endDate, $includeHQP, true);
        }
        if($type != "all"){
            $types = explode("|", $type);
            foreach($products as $key => $product){
                $type = explode(":", $product->getType());
                if(!in_array($type[0], $types)){
                    // Type doesn't match
                    unset($products[$key]);
                }
            }
        }
        if($data != ""){
            foreach($products as $key => $product){
                $productData = $product->getData();
                $datas = explode("=", $data);
                if(isset($productData[$datas[0]]) && $productData[$datas[0]] != $datas[1]){
                    // Data doesn't match
                    unset($products[$key]);
                }
            }
        }
        if($peerReviewed){
            foreach($products as $key => $product){
                if($product->getData('peer_reviewed') != "Yes"){
                    // Not Peer Reviewed
                    unset($products[$key]);
                }
            }
        }
        return count($products);
    }
    
    function getBlobMD5($rp="", $section="", $blobId="", $subId="", $personId="", $projectId="", $year=null){
        if($rp == ""){
            return $this->reportItem->getMD5();
        }
        if($year == null){
            $year = $this->reportItem->getReport()->year;
        }
        $address = ReportBlob::create_address($rp, $section, $blobId, $subId);
        $cacheId = "blob_md5_{$year}_{$personId}_{$projectId}_".implode("_", $address);
        if(!Cache::exists($cacheId)){
            $data = DBFunctions::execSQL("SELECT md5, encrypted 
                                          FROM grand_report_blobs 
                                          WHERE user_id = '{$personId}' 
                                          AND year = '{$year}'
                                          AND proj_id = '{$projectId}' 
                                          AND rp_type = '{$rp}' 
                                          AND rp_section = '{$section}' 
                                          AND rp_item = '{$blobId}' 
                                          AND rp_subitem = '{$subId}'");
            Cache::store($cacheId, $data);
        }
        else{
            $data = Cache::fetch($cacheId);
        }
		$md5 = (@$data[0]['encrypted']) ? encrypt(@$data[0]['md5']) : @$data[0]['md5'];
		if(!isset($_GET['preview']) && isset($_GET['generatePDF'])){
		    return urlencode(urlencode($md5));
		}
        return urlencode($md5);
    }
    
    function getArray($rp, $section, $blobId, $subId, $personId, $projectId, $index=null, $delim=", ", $year=null){
        $year = ($year == null) ? $this->reportItem->getReport()->year : $year;
        $addr = ReportBlob::create_address($rp, $section, $blobId, $subId);
        $blb = new ReportBlob(BLOB_ARRAY, $year, $personId, $projectId);
        $result = $blb->load($addr);
        if($index == null){
            return $blb->getData();
        }
        else{
            $array = $blb->getData();
            $index = explode("|", $index);
            foreach($index as $i){
                $array = @$array[$i];
            }
            $value = $array;
            if(is_array($value) && $delim != ""){
                return str_replace(")", "&#41;", str_replace("(", "&#40;", @implode($delim, $value)));
            }
            return $value;
        }
    }
    
    function getText($rp, $section, $blobId, $subId, $personId, $projectId, $year=null, $clean=false){
        $year = ($year == null) ? $this->reportItem->getReport()->year : $year;
        $addr = ReportBlob::create_address($rp, $section, $blobId, $subId);
        $blb = new ReportBlob(BLOB_TEXT, $year, $personId, $projectId);
        $result = $blb->load($addr);
        $data = nl2br($blb->getData());
        if($clean){
            $data = str_replace("(", "", $data);
            $data = str_replace(")", "", $data);
        }
        return $data;
    }
    
    function getNumber($rp, $section, $blobId, $subId, $personId, $projectId, $year=null){
        return (float) $this->getText($rp, $section, $blobId, $subId, $personId, $projectId, $year);
    }
    
    function getIncrement($personId, $year){
        $person = Person::newFromId($personId);
        return $person->getIncrement($year);
    }
    
    function occurrences($val1, $val2){
        return substr_count($val1, $val2);
    }
    
    function count($val){
        return count($val);
    }
    
    function concat(){
        $args = func_get_args();
        $concat = "";
        foreach($args as $arg){
            $concat .= $arg;
        }
        return $concat;
    }
    
    function removeSpaces($string){
        return str_replace(" ", "", $string);
    }
    
    function replace($pattern, $replacement, $string){
        return str_replace($pattern, $replacement, $string);
    }
    
    function strtolower($str){
        return strtolower($str);
    }
    
    function strtoupper($str){
        return strtoupper($str);
    }
    
    function substr($string, $offset, $length=null){
        return @substr($string, $offset, $length);
    }
    
    function alpha($str){
        // Converts number to letter (1 => a, 2 => b)
        return chr($str + 96);
    }
    
    function nl2br($str){
        return nl2br($str);
    }
    
    function add(){
        $args = func_get_args();
        $sum = 0;
        foreach($args as $arg){
            $arg = str_replace("&#44;", "", $arg);
            $sum += $arg;
        }
        return $sum;
    }
    
    function subtract($val1, $val2){
        $val1 = str_replace("&#44;", "", $val1);
        $val2 = str_replace("&#44;", "", $val2);
        return $val1 - $val2;
    }
    
    function multiply($val1, $val2){
        $val1 = str_replace("&#44;", "", $val1);
        $val2 = str_replace("&#44;", "", $val2);
        return $val1*$val2;
    }
    
    function divide($val1, $val2){
        $val1 = str_replace("&#44;", "", $val1);
        $val2 = str_replace("&#44;", "", $val2);
        return $val1/max(1, $val2);
    }
    
    function round($val, $dec=0){
        $val = str_replace("&#44;", "", $val);
        return number_format(round($val, $dec), $dec, ".", "");
    }
    
    function max(){
        $args = func_get_args();
        return max($args);
    }
    
    function min(){
        $args = func_get_args();
        return min($args);
    }
    
    function set($key, $val){
        $this->reportItem->setVariable($key, $val);
    }
    
    function get($key){
        return $this->reportItem->getVariable($key);
    }
    
    function ifCond($condition, $result){
        $value = false;
        if($condition == ""){
            $condition = "false";
        }
        @eval("\$value = ($condition);");
        if($value){
            return $result;
        }
        return "";
    }
    
    function andCond(){
        $bool = true;
        $arg_list = func_get_args();
        foreach($arg_list as $arg){
            $bool = ($bool && $arg);
        }
        return $bool;
    }
    
    function orCond(){
        $bool = false;
        $arg_list = func_get_args();
        foreach($arg_list as $arg){
            $bool = ($bool || $arg);
        }
        return $bool;
    }
    
    function contains($val1, $val2){
        return (strstr($val1, $val2) !== false);
    }
    
    function notContains($val1, $val2){
        return !$this->contains($val1, $val2);
    }
    
    function eq($val1, $val2){
        return ($val1 == $val2);
    }
    
    function neq($val1, $val2){
        return ($val1 != $val2);
    }
    
    function gt($val1, $val2){
        return ($val1 > $val2);
    }
    
    function lt($val1, $val2){
        return ($val1 < $val2);
    }
    
    function gteq($val1, $val2){
        return ($val1 >= $val2);
    }
    
    function lteq($val1, $val2){
        return ($val1 <= $val2);
    }
    
    function getHTML($rp, $section, $blobId, $subId, $personId, $projectId, $year=null){
        $year = ($year == null) ? $this->reportItem->getReport()->year : $year;
        $addr = ReportBlob::create_address($rp, $section, $blobId, $subId);
        $blb = new ReportBlob(BLOB_TEXT, $year, $personId, $projectId);
        $result = $blb->load($addr);
        $blobValue = $blb->getData();
        
        $blobValue = str_replace("</p>", "<br /><br style='font-size:1em;' />", $blobValue);
        $blobValue = str_replace("<p>", "", $blobValue);
        $blobValue = str_replace_last("<br /><br style='font-size:1em;' />", "", $blobValue);
        return "<div class='tinymce'>$blobValue</div>";
    }
    
    function getExtra($index=null){
        $set = $this->reportItem->extra;
        $ret = "";
        if($index == null){
            $ret = str_replace(")", "&#41;", 
                   str_replace("(", "&#40;", $set));
        }
        if(isset($set[$index])){
            $ret = str_replace(")", "&#41;", 
                   str_replace("(", "&#40;", $set[$index]));
        }
        if(is_array($ret)){
            $ret = implode("; ", $ret);
        }
        return $ret;
    }
    
    function strip_html($html){
        return strip_tags($html);
    }
    
    function getPostId(){
        return $this->reportItem->getPostId();
    }
    
    function getDate(){
        return date('F j, Y');
    }
    
    function getTimestamp(){
        $date = new DateTime("now", new DateTimeZone(date_default_timezone_get())); // USER's timezone
        return $date->format('Y-m-d H:i:s T');
    }
    
    function getSectionTitle(){
        return htmlentities($this->reportItem->getSection()->title);
    }
    
    function getReportName(){
        return $this->reportItem->getReport()->name;
    }
    
    function getReportXMLName(){
        return $this->reportItem->getReport()->xmlName;
    }
    
    function getReportSection(){
        return $this->reportItem->getSection()->name;
    }
    
    function getReportType(){
        return $this->reportItem->getSection()->getAttr("blobReport", $this->reportItem->getReport()->reportType);
    }
    
    function getUserHQPGraduatedCount($type, $year){
        $person = Person::newFromId($this->reportItem->personId);
        $data = $person->getStudentInfo(Person::$studentPositions[$type], $year."-01-01", $year."-12-31");
        $hqps = array();
        foreach($data as $row){
            if($row['status'] == "Completed" && substr($row['end_date'], 0, 4) == $year){
                $hqps[$row['hqp']] = $row;
            }
        }
        return count($hqps);
    }
    
    function getUserHQPCalendarCount($type, $year){
        $person = Person::newFromId($this->reportItem->personId);
        $data = $person->getStudentInfo(Person::$studentPositions[$type], $year."-01-01", $year."-12-31");
        $hqps = array();
        foreach($data as $row){
            $hqps[$row['hqp']] = $row;
        }
        return count($hqps);
    }
    
    function getUserHQPCount(){
        $person = Person::newFromId($this->reportItem->personId);
        $startDate = ($this->reportItem->getReport()->startYear).CYCLE_START_MONTH;
        $endDate = ($this->reportItem->getReport()->year).CYCLE_END_MONTH;
        
        $data = array_merge($person->getStudentInfo(Person::$studentPositions['grad'], $startDate, $endDate),
                            $person->getStudentInfo(Person::$studentPositions['msc'], $startDate, $endDate),
                            $person->getStudentInfo(Person::$studentPositions['phd'], $startDate, $endDate),
                            $person->getStudentInfo(Person::$studentPositions['pdf'], $startDate, $endDate),
                            $person->getStudentInfo(Person::$studentPositions['tech'], $startDate, $endDate),
                            $person->getStudentInfo(Person::$studentPositions['ugrad'], $startDate, $endDate),
                            $person->getStudentInfo('other', $startDate, $endDate));
        $hqps = array();
        foreach($data as $row){
            $hqps[$row['hqp']] = $row;
        }
        
        return count($hqps);
    }

    function getUserGradCount($status="all"){ // Status should be 'all' or 'current' or 'completed'
        return $this->getHQPCount('grad', $status);
    }
    
    function getUserMscCount($status="all"){ // Status should be 'all' or 'current' or 'completed'
        return $this->getHQPCount('msc', $status);
    }
    
    function getUserPhDCount($status="all"){ // Status should be 'all' or 'current' or 'completed'
        return $this->getHQPCount('phd', $status);
    }

    function getUserFellowCount($status="all"){ // Status should be 'all' or 'current' or 'completed'
        return $this->getHQPCount('pdf', $status);
    }
    
    function getUserTechCount($status="all"){ // Status should be 'all' or 'current' or 'completed'
        return $this->getHQPCount('tech', $status);
    }
    
    function getUserUgradCount($status="all"){ // Status should be 'all' or 'current' or 'completed'
        return $this->getHQPCount('ugrad', $status);
    }
    
    function getUserOtherCount($status="all"){ // Status should be 'all' or 'current' or 'completed'
        return $this->getHQPCount('other', $status);
    }
    
    function getUserCommitteeCount(){
        return $this->getHQPCount('committee');
    }
    
    function getHQPCount($type, $status="all"){
        $status = strtolower($status);
        $person = Person::newFromId($this->reportItem->personId);
        $startDate = ($this->reportItem->getReport()->startYear).CYCLE_START_MONTH;
        $endDate = ($this->reportItem->getReport()->year).CYCLE_END_MONTH;
        
        $type = (isset(Person::$studentPositions[$type])) ? Person::$studentPositions[$type] : $type;
        
        $data = $person->getStudentInfo($type, $startDate, $endDate);
        $hqps = array();
        foreach($data as $row){
            if($status == "all" || ($row['end_date'] == "Current" && $status == "current") || 
                                   ($row['end_date'] != "Current" && $status == "completed")){
                $hqps[$row['hqp'].$row['position']] = $row;
            }
        }
        return count($hqps);
    }
    
    function getTermsArray($start, $end, $delim="|"){
        $startYear = substr($start, 0, 4);
        $startMonth = substr($start, 5, 5);
        $endYear = substr($end, 0, 4);
        $endMonth = substr($end, 5, 5);
        
        $terms = array();
        
        if($startMonth != "01-01"){
            //  Last year
            if($startMonth <= "04-31"){ // April
                $terms[] = "Winter {$startYear}";
            }
            if($startMonth <= "06-31"){ // June
                $terms[] = "Spring {$startYear}";
            }
            if($startMonth <= "08-31"){ // August
                $terms[] = "Summer {$startYear}";
            }
            if($startMonth <= "12-31"){ // December
                $terms[] = "Fall {$startYear}";
            }
        }
        
        // This year
        if($endMonth >=  "01-01"){ // December
            $terms[] = "Winter {$endYear}";
        }
        if($endMonth >= "05-01"){ // May
            $terms[] = "Spring {$endYear}";
        }
        if($endMonth >= "07-01"){ // July
            $terms[] = "Summer {$endYear}";
        }
        if($endMonth >= "09-01"){ // Sept
            $terms[] = "Fall {$endYear}";
        }
        
        return implode($delim, $terms);
    }
    
    function getUserCoursesEnrolledByTerm($term){
        $person = Person::newFromId($this->reportItem->personId);
        $courses = $person->getCoursesDuring(SOT, EOT);
        $enrolled = 0;
        foreach($courses as $course){
            if($course->component == "LEC" && strstr($term, $course->term_string) !== false){
                $enrolled += $course->totEnrl;
            }
        }
        return $enrolled;
    }
    
    function getUserSectionCounts(){
        $course = Course::newFromId($this->reportItem->projectId);
        $person = Person::newFromId($this->reportItem->personId);
        $data = DBFunctions::execSQL("SELECT c.component
                                      FROM  `grand_courses` c, `grand_user_courses` uc
                                      WHERE c.id = uc.course_id
                                      AND uc.user_id = '{$person->getId()}'
                                      AND c.term_string = '{$course->term_string}'
                                      AND c.subject = '{$course->subject}'
                                      AND c.catalog = '{$course->catalog}'
                                      AND uc.percentage != '0'");
        $sections = array('LEC' => 0, 'SEM' => 0, 'LAB' => 0);
        foreach($data as $row){
            @$sections[$row['component']]++;
        }
        $sectionsStr = array();
        foreach($sections as $sec => $count){
            if($count > 0){
                $sectionsStr[] = "$sec: $count";
            }
        }
        return implode(", ", $sectionsStr);
    }
    
    function getUserCoursesCount(){
        $person = Person::newFromId($this->reportItem->personId);
        $courses = $person->getCoursesDuring(($this->reportItem->getReport()->startYear).CYCLE_START_MONTH, ($this->reportItem->getReport()->year).CYCLE_END_MONTH);
        $count = 0;
        foreach($courses as $course){
            if($course->totEnrl > 0){
                $count++;
            }
        }
        return $count;
    }
    
    function getUserStudentCount(){
        $person = Person::newFromId($this->reportItem->personId);
        $courses = $person->getCoursesDuring(($this->reportItem->getReport()->startYear).CYCLE_START_MONTH, ($this->reportItem->getReport()->year).CYCLE_END_MONTH);
        $count = 0;
        foreach($courses as $course){
            $count += $course->totEnrl;
        }
        return $count;
    }
    
    function getUserLecCount(){
        $person = Person::newFromId($this->reportItem->personId);
        $courses = $person->getCoursesDuring(($this->reportItem->getReport()->startYear).CYCLE_START_MONTH, ($this->reportItem->getReport()->year).CYCLE_END_MONTH);
        $count = 0;
        foreach($courses as $course){
            if($course->totEnrl > 0 && $course->component == "LEC"){
                $count++;
            }
        }
        return $count;
    }
    
    function getUserLecStudentCount(){
        $person = Person::newFromId($this->reportItem->personId);
        $courses = $person->getCoursesDuring(($this->reportItem->getReport()->startYear).CYCLE_START_MONTH, ($this->reportItem->getReport()->year).CYCLE_END_MONTH);
        $count = 0;
        foreach($courses as $course){
            if($course->component == "LEC"){
                $count += $course->totEnrl;
            }
        }
        return $count;
    }
    
    function getUserGrantCount(){
        $person = Person::newFromId($this->reportItem->personId);
        $grants = $person->getGrantsBetween(($this->reportItem->getReport()->startYear).CYCLE_START_MONTH, ($this->reportItem->getReport()->year).CYCLE_END_MONTH);
        return count($grants);
    }
    
    function getUserCVGrantCount(){
        $person = Person::newFromId($this->reportItem->personId);
        $grants = $person->getGrantsBetween(($this->reportItem->getReport()->startYear).CYCLE_START_MONTH, ($this->reportItem->getReport()->year).CYCLE_END_MONTH, true);
        return count($grants);
    }

    function getUserGrantTotal(){
        $person = Person::newFromId($this->reportItem->personId);
        $grants = $person->getGrantsBetween(($this->reportItem->getReport()->startYear).CYCLE_START_MONTH, ($this->reportItem->getReport()->year).CYCLE_END_MONTH);
        $total = 0;
        foreach($grants as $grant){
            $total += $grant->getTotal();
        }
        return number_format($total, 2);
    }

    function getUserPhdYear(){
        $person = Person::newFromId($this->reportItem->personId);
        if($person->getId() == null){
            $person = $this->reportItem->getReport()->person;
        }
        $fecInfo = $person->getFecPersonalInfo();
        $phd_year_array = explode("-", $fecInfo->dateOfPhd);
        return $phd_year_array[0];
    }

    function getUserAppointmentYear(){
        $person = Person::newFromId($this->reportItem->personId);
        if($person->getId() == null){
            $person = $this->reportItem->getReport()->person;
        }
        $fecInfo = $person->getFecPersonalInfo();
        $phd_year_array = explode("-", $fecInfo->dateOfAppointment);
        return $phd_year_array[0];
    }
    
    function getUserPhdDate(){
        $person = Person::newFromId($this->reportItem->personId);
        if($person->getId() == null){
            $person = $this->reportItem->getReport()->person;
        }
        $fecInfo = $person->getFecPersonalInfo();
        return substr($fecInfo->dateOfPhd, 0, 10);
    }

    function getUserAppointmentDate(){
        $person = Person::newFromId($this->reportItem->personId);
        if($person->getId() == null){
            $person = $this->reportItem->getReport()->person;
        }
        $fecInfo = $person->getFecPersonalInfo();
        return substr($fecInfo->dateOfAppointment, 0, 10);
    }
    
    function getUserATSAnniversaryDate(){
        $person = Person::newFromId($this->reportItem->personId);
        if($person->getId() == null){
            $person = $this->reportItem->getReport()->person;
        }
        $fecInfo = $person->getFecPersonalInfo();
        return substr($fecInfo->dateAtsAnniversary, 0, 10);
    }
    
    function getATSYear(){
        $today = date('Y-m-d', time() + 86400*7*6); // 6 weeks into the future
        $date = date('Y-m-d', strtotime($this->getUserATSAnniversaryDate()) - 86400); // anniversary - 1 day
        $year  = substr($date, 0, 4);
        $month = substr($date, 5, 5);
        if(!is_numeric($year)){
            return YEAR;
        }
        while("$year-$month" <= $today){
            $year++;
        }
        return $year - 1;
    }
    
    function getATSStartDate(){
        $year = $this->getATSYear() - 1;
        $date = $this->getUserATSAnniversaryDate();
        $date = ($date != "") ? $date : CYCLE_START;
        $month = substr($date, 5, 5);
        return "$year-$month";
    }
    
    function getATSEndDate(){
        $year = $this->getATSYear();
        $date = $this->getUserATSAnniversaryDate();
        $date = ($date != "") ? $date : CYCLE_START;
        $month = substr($date, 5, 5);
        return date('Y-m-d', strtotime("$year-$month -1 day"));
    }
    
    function getFecPersonalInfo($field, $formatAsDate=true){
        $person = Person::newFromId($this->reportItem->personId);
        $fecInfo = $person->getFecPersonalInfo();
        return ($formatAsDate) ? substr($fecInfo->{$field}, 0, 10) : $fecInfo->{$field};
    }
    
    function getUserPhDUni(){
        $person = Person::newFromId($this->reportItem->personId);
        $unis = $person->getUniversities();
        foreach($unis as $uni){
            if(in_array(strtolower($uni['position']), Person::$studentPositions['phd'])){
                return $uni['university'];
            } 
        }
        return "";
    }
}

ReportItemCallback::$reportCallback = new ReportItemCallback(null);

?>
