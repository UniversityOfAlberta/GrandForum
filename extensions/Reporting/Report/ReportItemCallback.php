<?php

class ReportItemCallback {
    
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
            // Courses
            "course_term" => "getCourseTerm",
            "course_start" => "getCourseStart",
            "course_end" => "getCourseEnd",
            "course_subject" => "getCourseSubject",
            "course_number" => "getCourseNumber",
            "course_title" => "getCourseTitle",
            "course_comp" => "getCourseComp",
            "course_section" => "getCourseSection",
            "course_enroll" => "getCourseEnroll",    
            "course_enroll_percent" => "getCourseEnrollPercent",
            // Student Relation
            "hqp_name" => "getHqpName",
            "hqp_reversed_name" => "getHqpReversedName",
            "hqp_position" => "getHqpPosition",
            "hqp_awards" => "getHqpAwards",
            "user_hqp_role" => "getUserHqpRole",
            "hqp_start_date" => "getHqpStartDate",
            "hqp_end_date" => "getHqpEndDate",
            "hqp_status" => "getHqpStatus",
            "hqp_research_area" => "getHqpResearchArea",
            // Contributions
            "contribution_scope" => "getContributionScope",
            "contribution_agency" => "getContributionAgency",
            "contribution_program" => "getContributionProgram",
            "contribution_start_date" => "getContributionStartDate",
            "contribution_end_date" => "getContributionEndDate",
            "contribution_yearly" => "getContributionYearly",
            "contribution_total" => "getContributionTotal",
            "contribution_recipients" => "getContributionRecipients",
            "contribution_pis" => "getContributionPIs",
            // Grants
            "grant_title" => "getGrantTitle",
            "grant_description" => "getGrantDescription",
            "grant_sponsor" => "getGrantSponsor",
            "grant_project_id" => "getGrantProjectId",
            "grant_start_date" => "getGrantStartDate",
            "grant_end_date" => "getGrantEndDate",
            "grant_total" => "getGrantTotal",
            // Reports
            "timestamp" => "getTimestamp",
            "post_id" => "getPostId",
            "report_name" => "getReportName",
            "report_xmlname" => "getReportXMLName",
            "section_name" => "getSectionName",
            "report_has_started" => "getReportHasStarted",
            // People
            "my_id" => "getMyId",
            "my_name" => "getMyName",
            "my_first_name" => "getMyFirstName",
            "my_last_name" => "getMyLastName",
            "parent_id" => "getParentId",
            "parent_name" => "getParentName",
            "parent_uni" => "getParentUni",
            "user_name" => "getUserName",
            "user_url" => "getUserUrl",
            "user_email" => "getUserEmail",
            "user_phone" => "getUserPhone",
            "user_gender" => "getUserGender",
            "user_reversed_name" => "getUserReversedName",
            "user_last_name" => "getUserLastName",
            "user_first_name" => "getUserFirstName",
            "user_id" => "getUserId",
            "user_roles" => "getUserRoles",
            "user_full_roles" => "getUserFullRoles",
            "user_level" => "getUserLevel",
            "user_dept" => "getUserDept",
            "user_uni" => "getUserUni",
            "user_nationality" => "getUserNationality",
            "user_supervisors" => "getUserSupervisors",
            "user_product_count" => "getUserProductCount",
            "user_grad_count" => "getUserGradCount",
            "user_fellow_count" => "getUserFellowCount",
            "user_tech_count" => "getUserTechCount",
            "user_ugrad_count" => "getUserUgradCount",
            "user_courses_count" => "getUserCoursesCount",
            "user_contribution_count" => "getUserContributionCount",
            "user_contribution_cash_total" => "getUserContributionCashTotal",
            "user_grant_count" => "getUserGrantCount",
            "user_grant_total" => "getUserGrantTotal",
            "user_phd_year" => "getUserPhdYear",
            "user_appointment_year" => "getUserAppointmentYear",
            "getUserPublicationCount" => "getUserPublicationCount",
            "user_lifetime_pubs_count" => "getUserLifetimePublicationCount",
            // ISAC
            "chair_id" => "getChairId",
            // Products
            "product_id" => "getProductId",
            "product_type" => "getProductType",
            "product_title" => "getProductTitle",
            "product_description" => "getProductDescription",
            "product_url" => "getProductUrl",
            "product_citation" => "getProductCitation",
            "product_qa_citation" => "getProductQACitation",
            "product_date" => "getProductDate",
            "product_acceptance_year" => "getProductAcceptanceYear",
            "product_year" => "getProductYear",
            "product_year_range" => "getProductYearRange",
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
            // Other
            "wgUserId" => "getWgUserId",
            "wgServer" => "getWgServer",
            "wgScriptPath" => "getWgScriptPath",
            "GET" => "getGet",
            "networkName" => "getNetworkName",
            "id" => "getId",
            "name" => "getName",
            "index" => "getIndex",
            "extraIndex" => "getExtraIndex",
            "getNProducts" => "getNProducts",
            "getBlobMD5" => "getBlobMD5",
            "getText" => "getText",
            "getNumber" => "getNumber",
            "getHTML" => "getHTML",
            "getArray" => "getArray",
            "getExtra" => "getExtra",
            "count" => "count",
            "add" => "add",
            "subtract" => "subtract",
            "multiply" => "multiply",
            "divide" => "divide",
            "round" => "round",
            "set" => "set",
            "get" => "get",
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
    function ReportItemCallback($reportItem){
        $this->reportItem = $reportItem;
    }
    
    function get2YearsAgo(){
        return $this->reportItem->getReport()->year-2;
    }
    
    function getStartYear(){
        return $this->reportItem->getReport()->startYear;
    }
    
    function getLastYear(){
        return $this->reportItem->getReport()->year-1;
    }
    
    function getThisYear(){
        return $this->reportItem->getReport()->year;
    }
    
    function getNextYear(){
        return $this->reportItem->getReport()->year+1;
    }
    
    function getNextYear2(){
        return $this->reportItem->getReport()->year+2;
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
            $award_names[] = str_replace("Misc: ", "", $award->type);
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
        return str_replace("0000-00-00", "", $array[0]);
    }
    
    function getHqpEndDate(){
        $relation = Relationship::newFromId($this->reportItem->projectId);
        $array = explode(" ", $relation->getEndDate());
        return str_replace("0000-00-00", "", $array[0]);
    }
    
    function getHqpResearchArea(){
        $relation = Relationship::newFromId($this->reportItem->projectId);
        $hqp = $relation->getUser2();
        return $hqp->getResearchArea();
    }

    function getHqpStatus(){
        if($this->getHqpEndDate() == '0000-00-00' || $this->getHqpEndDate() == ''){
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

    function getCourseEnroll(){
        $course = Course::newFromId($this->reportItem->projectId);
        return $course->totEnrl;
    }

    function getCourseEnrollPercent(){
        $course = Course::newFromId($this->reportItem->projectId);
        return ($course->totEnrl/max(1,$course->capEnrl))*100;
    }

    function getContributionAgency(){
        $contribution = Contribution::newFromId($this->reportItem->projectId);
        $partners = $contribution->getPartners();
        $contribution_string = array();
        foreach($partners as $partner){
            $contribution_string[] = $partner->organization; 
        }
        return implode(",", $contribution_string);
    }

    function getContributionProgram(){
        $contribution = Contribution::newFromId($this->reportItem->projectId);
        return $contribution->name;
    }

    function getContributionScope(){
        $contribution = Contribution::newFromId($this->reportItem->projectId);
        return $contribution->scope;
    }

    function getContributionStartDate(){
        $contribution = Contribution::newFromId($this->reportItem->projectId);
        $array = explode(" ",$contribution->start_date);
        return $array[0];
    }   

    function getContributionEndDate(){
        $contribution = Contribution::newFromId($this->reportItem->projectId);
        $array = explode(" ", $contribution->end_date);
        return $array[0];
    }

    function getContributionYearly(){
        $contribution = Contribution::newFromId($this->reportItem->projectId);
        $end = new DateTime($contribution->end_date);
        $start = new DateTime($contribution->start_date);
        $total = $contribution->getTotal();
        $diff = $end->diff($start);
        if(($diff->y) != 0){
            if(($diff->m) >6){
                    $yearly = $total/(($diff->y)+1);
            }
            else{
            $yearly = $total/(($diff->y));
            }
                return number_format($yearly);
        }
        return number_format($contribution->getTotal());
    }   

    function getContributionTotal(){
        $contribution = Contribution::newFromId($this->reportItem->projectId);
        return number_format($contribution->getTotal());
    }   

    function getContributionRecipients(){
        $contribution = Contribution::newFromId($this->reportItem->projectId);
        $recipients = $contribution->getPeople();
        $string_names = array();
        foreach($recipients as $recipient){
            if($recipient instanceof Person){
                $string_names[] = $recipient->getNameForForms();
            }
            else{
                $string_names[] = $recipient;
            }
        }
        return implode(";",$string_names);
    }  


    function getContributionPIs(){
        $contribution = Contribution::newFromId($this->reportItem->projectId);
        $recipients = $contribution->getPIs();
        $string_names = array();
        foreach($recipients as $recipient){
            if($recipient instanceof Person){
                $string_names[] = $recipient->getNameForForms();
            }
            else{
                $string_names[] = $recipient;
            }
        }
        return implode(";",$string_names);
    }
 
    function getGrantTitle(){
        $grant = Grant::newFromId($this->reportItem->productId);
        return $grant->getTitle();
    }
    
    function getGrantDescription(){
        $grant = Grant::newFromId($this->reportItem->productId);
        return $grant->getDescription();
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
    
    function getReportHasStarted(){
        $report = $this->reportItem->getReport();
        if($report->hasStarted()){
            return "<span style='font-weight:bold;color:#008800;'>Yes</span>";
        }
        return "<span>No</span>";
    }
    
    private function getReportNIComments($item){
        if($this->reportItem->projectId != 0){
            $project = Project::newFromId($this->reportItem->projectId);
            $ni_rep_addr = ReportBlob::create_address(RP_RESEARCHER, RES_RESACTIVITY, $item, 0);
        }
        else{
            return;
        }
        $nis = $project->getAllPeopleDuring(NI, REPORTING_YEAR."-01-01 00:00:00", REPORTING_YEAR."-12-31 23:59:59");
        $ni_comments = "";
        $alreadyDone = array();
        foreach($nis as $ni){
            if(isset($alreadyDone[$ni->getId()])){
                continue;
            }
            $alreadyDone[$ni->getId()] = true;
            $ni_blob = new ReportBlob(BLOB_TEXT, REPORTING_YEAR, $ni->getId(), $project->getId());
            $ni_blob->load($ni_rep_addr);
            $ni_data = $ni_blob->getData();
            if($ni_data != null){
                $ni_data = preg_replace("/@\[[^-]+-([^\]]*)]/", "<b>$1</b>$2", $ni_data);
                $ni_comments .= $ni->getReversedName() . ":<br /><i style='margin:10px;display:block;'>" . 
                        $ni_data . "</i><br />";
            }
        }
        return $ni_comments;
    }
    
    private function getReportHQPComments($item){
        if($this->reportItem->projectId != 0){
            $project = Project::newFromId($this->reportItem->projectId);
            $hqp_rep_addr = ReportBlob::create_address(RP_HQP, HQP_RESACTIVITY, $item, 0);
        }
        else{
            $project = null;
            $hqp_rep_addr = ReportBlob::create_address(RP_HQP, HQP_RESACTIVITY, $item, RES_RESACT_PHASE1);
        }
        $me = Person::newFromId($this->reportItem->personId);
        
        $hqps = $me->getHQPDuring(REPORTING_CYCLE_START, REPORTING_CYCLE_END);
        $hqp_comments = "";
        foreach($hqps as $hqp){
            if($this->reportItem->projectId == 0 || $hqp->isMemberOfDuring($project, REPORTING_CYCLE_START, REPORTING_CYCLE_END)){
                $hqp_blob = new ReportBlob(BLOB_TEXT, REPORTING_YEAR, $hqp->getId(), $this->reportItem->projectId);
                $hqp_blob->load($hqp_rep_addr);
                $hqp_data = $hqp_blob->getData();
                if($hqp_data != null){
                    $hqp_comments = preg_replace("/@\[[^-]+-([^\]]*)]/", "<b>$1</b>$2", $hqp_comments);
                    $hqp_comments .= $hqp->getReversedName() . ":<br /><i style='margin:10px;display:block;'>" . 
                            $hqp_data . "</i><br />";
                }
            }
        }
        return $hqp_comments;
    }
    
    function getMyId(){
        $person = $this->reportItem->getReport()->person;
        return $person->getId();
    }
    
    function getMyName(){
        $person = $this->reportItem->getReport()->person;
        return $person->getNameForForms();
    }
    
    function getMyFirstName(){
        $person = $this->reportItem->getReport()->person;
        return $person->getFirstName();
    }
    
    function getMyLastName(){
        $person = $this->reportItem->getReport()->person;
        return $person->getLastName();
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
    
    function getUserGender(){
        $person = Person::newFromId($this->reportItem->personId);
        return $person->getGender();
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
    
    function getUserRoles(){
        $person = Person::newFromId($this->reportItem->personId);
        $project = Project::newFromId($this->reportItem->projectId);
        $roles = $person->getRoles();
        $roleNames = array();
        foreach($roles as $role){
            if($project != null && $project->getId() != 0){
                if($role->hasProject($project)){
                    $roleNames[$role->getRole()] = $role->getRole();
                }
            }
            else{
                $roleNames[$role->getRole()] = $role->getRole();
            }
        }
        return implode(", ", $roleNames);
    }
    
    function getUserFullRoles(){
        $person = Person::newFromId($this->reportItem->personId);
        $project = Project::newFromId($this->reportItem->projectId);
        $roles = $this->getUserRoles();
        if($project != null && $project->getId() != 0){
            if($person->leadershipOf($project)){
                if($roles != ""){
                    $roles .= ", PL";
                }
                else{
                    $roles .= "PL";
                }
            }
        }
        else if($person->isProjectLeader()){
            if($roles != ""){
                $roles .= ", PL";
            }
            else{
                $roles .= "PL";
            }
        }
        return $roles;
    }
    
    function getUserLevel(){
        $person = Person::newFromId($this->reportItem->personId);
        $university = $person->getUniversity();
        return $university['position'];
    }
    
    function getUserDept(){
        $person = Person::newFromId($this->reportItem->personId);
        $university = $person->getUniversity();
        return $university['department'];
    }
    
    function getUserUni(){
        $person = Person::newFromId($this->reportItem->personId);
        $university = $person->getUniversity();
        return $university['university'];
    }
    
    function getUserNationality(){
        $person = Person::newFromId($this->reportItem->personId);
        $nationality = $person->getNationality();
        return $nationality;
    }
    
    function getUserSupervisors(){
        $supervisors = array();
        $person = Person::newFromId($this->reportItem->personId);
        $me = $person;
        foreach(Person::getAllPeople('all') as $person){
            foreach($person->getRelations(SUPERVISES, true) as $rel){
                $start = $rel->getStartDate();
                $end = $rel->getEndDate();
                if($rel->getUser2()->getId() == $me->getId()){
                    if((strcmp($start, REPORTING_CYCLE_START."00:00:00") <= 0 && (strcmp($end, REPORTING_CYCLE_START."00:00:00") >= 0 || strcmp($end, "0000-00-00 00:00:00") == 0)) ||
                       (strcmp($start, REPORTING_CYCLE_END."00:00:00") <= 0 && strcmp($start, REPORTING_CYCLE_START."00:00:00") >= 0) ||
                       (strcmp($end, REPORTING_CYCLE_END."00:00:00") <= 0 && strcmp($end, REPORTING_CYCLE_START."00:00:00") >= 0)){
                        $sup = $rel->getUser1();
                        $supervisors[$sup->getId()] = "<a target='_blank' href='{$sup->getUrl()}'>{$sup->getNameForForms()}</a>";
                    }
                }
            }
        }
        return implode("; ", $supervisors);
    }

    function getUserPublicationCount($start_date,$end_date,$type='Publication'){
        $year = substr($end_date, 0, 4);
        $person = Person::newFromId($this->reportItem->personId);
        switch($type){
            default:
            case "Publication":
                $histories = $person->getProductHistories($year, "Refereed");
                break;
            case "Book":
                $histories = $person->getProductHistories($year, "Book");
                break;
            case "Patent":
                $histories = $person->getProductHistories($year, "Patent");
                break;
        }
        
        if(count($histories) > 0){
            return $histories[0]->getValue();
        }
        $products = $person->getPapersAuthored($type, $start_date, $end_date, true);
        return count($products);
    }

    function getUserLifetimePublicationCount($type='all'){
        $phdYear = max("1900", $this->getUserPhdYear());
        $year = $this->reportItem->getReport()->year;
        $person = Person::newFromId($this->reportItem->personId);
        
        $count = 0;
        for($y=$phdYear; $y <= $year; $y++){
            switch($type){
                default:
                case "Publication":
                    $previousCounts = $person->getProductHistories($y, "Previous Refereed");
                    break;
                case "Book":
                    $previousCounts = $person->getProductHistories($y, "Previous Book");
                    break;
                case "Patent":
                    $previousCounts = $person->getProductHistories($y, "Previous Patent");
                    break;
            }
            if(count($previousCounts) > 0){
                // Reset the count
                $count = $previousCounts[0]->getValue();
            }
            $count += $this->getUserPublicationCount(($y-1)."-07-01",($y)."-06-30",$type);
        }
        return $count;
    }

    function getChairId(){
        $user = Person::newFromId($this->reportItem->personId);
        $people = Person::getAllPeople(ISAC);
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
        return $product->getType();
    }
    
    function getProductTitle(){
        $product = Paper::newFromId($this->reportItem->productId);
        return $product->getTitle();
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
        return $product->getCitation(true, true, false);
    }
    
    function getProductQACitation(){
        $product = Paper::newFromId($this->reportItem->productId);
        return $product->getCitation(true, false, false);
    }

    function getPresentationTitle(){
        $product = Paper::newFromId($this->reportItem->productId);
        return $product->getTitle();
    }
    
    function getPresentationType(){
        $product = Paper::newFromId($this->reportItem->productId);
        return $product->getType();
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
        return @$product['location'];
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
    
    function getProductAcceptanceYear(){
        $product = Paper::newFromId($this->reportItem->productId);
        return @$product->getAcceptanceYear();
    }
    
    function getProductYear(){
        $product = Paper::newFromId($this->reportItem->productId);
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
            return $startYear;
        }
        else{
            return "{$startYear} - {$endYear}";
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

    function getGet($var1){
        if(isset($_GET[$var1])){
            return $_GET[$var1];
        }
        return "";
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
    
    function getName(){
        $personId = $this->reportItem->personId;
        $projectId = $this->reportItem->projectId;
        $productId = $this->reportItem->productId;
        
        if($personId != 0){
            $person = Person::newFromId($personId);
            return $person->getNameForForms();
        }
        else if($projectId != 0){
            $project = Project::newFromId($projectId);
            return $project->getName();
        }
        else if($productId != 0){
            $product = Product::newFromId($productId);
            return $product->getTitle();
        }
    }
    
    function getIndex(){
        $personId = $this->reportItem->personId;
        $projectId = $this->reportItem->projectId;
        $productId = $this->reportItem->productId;
        $milestoneId = $this->reportItem->milestoneId;
        $set = $this->reportItem->getSet();
        $i = 1;
        foreach($set->getData() as $item){
            if($item['milestone_id'] == $milestoneId &&
               $item['project_id'] == $projectId &&
               $item['person_id'] == $personId &&
               $item['product_id'] == $productId){
                return $i;
            }
            $i++;
        }
        return 0;
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
    
    function getNProducts($startDate = false, $endDate = false, $category="all", $type="all", $data="", $includeHQP="true"){
        $products = array();
        $includeHQP = (strtolower($includeHQP) == "true");
        if($this->reportItem->projectId != 0){
            // Project Products
            $project = Project::newFromId($this->reportItem->projectId);
            $products = $project->getPapers($category, $startDate, $endDate);
        }
        else if($this->reportItem->personId != 0){
            // Person Products
            $person = Person::newFromId($this->reportItem->personId);
            $products = $person->getPapersAuthored($category, $startDate, $endDate, $includeHQP, true);
        }
        if($type != "all"){
            $types = explode("|", $type);
            foreach($products as $key => $product){
                if(!in_array($product->getType(), $types)){
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
        return count($products);
    }
    
    function getBlobMD5($rp, $section, $blobId, $subId, $personId, $projectId){
        $addr = ReportBlob::create_address($rp, $section, $blobId, $subId);
        $blb = new ReportBlob(BLOB_PDF, $this->reportItem->getReport()->year, $personId, $projectId);
        $result = $blb->load($addr, true);
        return $blb->getMD5();
    }
    
    function getArray($rp, $section, $blobId, $subId, $personId, $projectId, $index=null){
        $addr = ReportBlob::create_address($rp, $section, $blobId, $subId);
        $blb = new ReportBlob(BLOB_ARRAY, $this->reportItem->getReport()->year, $personId, $projectId);
        $result = $blb->load($addr);
        if($index == null){
            return $blb->getData();
        }
        else{
            $array = $blb->getData();
            return @$array[$index];
        }
    }
    
    function getText($rp, $section, $blobId, $subId, $personId, $projectId){
        $addr = ReportBlob::create_address($rp, $section, $blobId, $subId);
        $blb = new ReportBlob(BLOB_TEXT, $this->reportItem->getReport()->year, $personId, $projectId);
        $result = $blb->load($addr);
        return nl2br($blb->getData());
    }
    
    function getNumber($rp, $section, $blobId, $subId, $personId, $projectId){
        return (float) $this->getText($rp, $section, $blobId, $subId, $personId, $projectId);
    }
    
    function count($val){
        return count($val);
    }
    
    function add($val1, $val2){
        return $val1 + $val2;
    }
    
    function subtract($val1, $val2){
        return $val1 - $val2;
    }
    
    function multiply($val1, $val2){
        return $val1*$val2;
    }
    
    function divide($val1, $val2){
        return $val1/max(1, $val2);
    }
    
    function round($val, $dec=0){
        return number_format(round($val, $dec), $dec, ".", "");
    }
    
    function set($key, $val){
        $this->reportItem->setVariable($key, $val);
    }
    
    function get($key){
        return $this->reportItem->getVariable($key);
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
    
    function getHTML($rp, $section, $blobId, $subId, $personId, $projectId){
        $addr = ReportBlob::create_address($rp, $section, $blobId, $subId);
        $blb = new ReportBlob(BLOB_TEXT, $this->reportItem->getReport()->year, $personId, $projectId);
        $result = $blb->load($addr);
        $blobValue = $blb->getData();
        
        $blobValue = str_replace("</p>", "<br /><br style='font-size:1em;' />", $blobValue);
        $blobValue = str_replace("<p>", "", $blobValue);
        $blobValue = str_replace_last("<br /><br style='font-size:1em;' />", "", $blobValue);
        return "<div class='tinymce'>$blobValue</div>";
    }
    
    function getExtra($index){
        $set = $this->reportItem->extra;
        if(isset($set[$index])){
            return $set[$index];
        }
        return "";
    }
    
    function getPostId(){
        return $this->reportItem->getPostId();
    }
    
    function getTimestamp(){
        $date = new DateTime("now", new DateTimeZone(date_default_timezone_get())); // USER's timezone
        return $date->format('Y-m-d H:i:s T');
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

    function getUserProductCount(){
        $person = Person::newFromId($this->reportItem->personId);
        $products = $person->getPapersAuthored('all', ($this->reportItem->getReport()->startYear)."-07-01", ($this->reportItem->getReport()->year)."-06-30", true);
        $products = $person->getPapers("all", false, 'both', true, "Public");
        return count($products);
    }

    function getUserGradCount(){
        $person = Person::newFromId($this->reportItem->personId);
        $relations = array_merge(
            $person->getRelationsDuring(SUPERVISES, ($this->reportItem->getReport()->startYear)."-07-01", ($this->reportItem->getReport()->year)."-06-30"),
            $person->getRelationsDuring(CO_SUPERVISES, ($this->reportItem->getReport()->startYear)."-07-01", ($this->reportItem->getReport()->year)."-06-30")
        );
        $count = 0;
        foreach($relations as $relation){
            if($relation->getEndDate() != "0000-00-00 00:00:00"){
                $university = $relation->getUser2()->getUniversityDuring($relation->getEndDate(), $relation->getEndDate());
            }
            else{
                $university = $relation->getUser2()->getUniversity();
            }
            if(in_array(strtolower($university['position']), array("phd","msc","phd student", "msc student", "graduate student - master's course", "graduate student - master's thesis", "graduate student - master's", "graduate student - master&#39;s", "graduate student - doctoral"))){
                
                $count++;
            }
        }
        return $count;
    }

    function getUserFellowCount(){
        $person = Person::newFromId($this->reportItem->personId);
        $relations = array_merge(
            $person->getRelationsDuring(SUPERVISES, ($this->reportItem->getReport()->startYear)."-07-01", ($this->reportItem->getReport()->year)."-06-30"),
            $person->getRelationsDuring(CO_SUPERVISES, ($this->reportItem->getReport()->startYear)."-07-01", ($this->reportItem->getReport()->year)."-06-30")
        );
        $count = 0;
        foreach($relations as $relation){
            if($relation->getEndDate() != "0000-00-00 00:00:00"){
                $university = $relation->getUser2()->getUniversityDuring($relation->getEndDate(), $relation->getEndDate());
            }
            else{
                $university = $relation->getUser2()->getUniversity();
            }
            if(in_array(strtolower($university['position']), array("pdf","post-doctoral fellow"))){
                $count++;
            }
        }
        return $count;
    }
    
    function getUserTechCount(){
        $person = Person::newFromId($this->reportItem->personId);
        $relations = array_merge(
            $person->getRelationsDuring(SUPERVISES, ($this->reportItem->getReport()->startYear)."-07-01", ($this->reportItem->getReport()->year)."-06-30"),
            $person->getRelationsDuring(CO_SUPERVISES, ($this->reportItem->getReport()->startYear)."-07-01", ($this->reportItem->getReport()->year)."-06-30")
        );
        $count = 0;
        foreach($relations as $relation){
            if($relation->getEndDate() != "0000-00-00 00:00:00"){
                $university = $relation->getUser2()->getUniversityDuring($relation->getEndDate(), $relation->getEndDate());
            }
            else{
                $university = $relation->getUser2()->getUniversity();
            }
            if(in_array(strtolower($university['position']), array("technician", "ra", "research/technical assistant", "professional end user"))){
                $count++;
            }
        }
        return $count;
    }
    
    function getUserUgradCount(){
        $person = Person::newFromId($this->reportItem->personId);
        $relations = array_merge(
            $person->getRelationsDuring(SUPERVISES, ($this->reportItem->getReport()->startYear)."-07-01", ($this->reportItem->getReport()->year)."-06-30"),
            $person->getRelationsDuring(CO_SUPERVISES, ($this->reportItem->getReport()->startYear)."-07-01", ($this->reportItem->getReport()->year)."-06-30")
        );
        $count = 0;
        foreach($relations as $relation){
            if($relation->getEndDate() != "0000-00-00 00:00:00"){
                $university = $relation->getUser2()->getUniversityDuring($relation->getEndDate(), $relation->getEndDate());
            }
            else{
                $university = $relation->getUser2()->getUniversity();
            }
            if(in_array(strtolower($university['position']), array("ugrad", "undergraduate", "undergraduate student"))){
                $count++;
            }
        }
        return $count;
    }
    
    function getUserCoursesCount(){
        $person = Person::newFromId($this->reportItem->personId);
        $courses = $person->getCoursesDuring(($this->reportItem->getReport()->startYear)."-07-01", ($this->reportItem->getReport()->year)."-06-30");
        $count = 0;
        foreach($courses as $course){
            if($course->totEnrl > 0){
                $count++;
            }
        }
        return $count;
    }
    
    function getUserContributionCount(){
        $person = Person::newFromId($this->reportItem->personId);
        $contributions = $person->getContributionsBetween(($this->reportItem->getReport()->startYear)."-07-01", ($this->reportItem->getReport()->year)."-06-30");
        return count($contributions);
    }

    function getUserContributionCashTotal(){
        $person = Person::newFromId($this->reportItem->personId);
        $contributions = $person->getContributionsBetween(($this->reportItem->getReport()->startYear)."-07-01", ($this->reportItem->getReport()->year)."-06-30");
        $total = 0;
        foreach($contributions as $contribution){
            $total += $contribution->getTotal();
        }
        return number_format($total);
    }
    
    function getUserGrantCount(){
        $person = Person::newFromId($this->reportItem->personId);
        $grants = $person->getGrantsBetween(($this->reportItem->getReport()->startYear)."-07-01", ($this->reportItem->getReport()->year)."-06-30");
        return count($grants);
    }

    function getUserGrantTotal(){
        $person = Person::newFromId($this->reportItem->personId);
        $grants = $person->getGrantsBetween(($this->reportItem->getReport()->startYear)."-07-01", ($this->reportItem->getReport()->year)."-06-30");
        $total = 0;
        foreach($grants as $grant){
            $total += $grant->getTotal();
        }
        return number_format($total, 2);
    }

    function getUserPhdYear(){
        $person = Person::newFromId($this->reportItem->personId);
        $fecInfo = $person->getFecPersonalInfo();
        $phd_year_array = explode("-", $fecInfo->dateOfPhd);
        return $phd_year_array[0];
    }

    function getUserAppointmentYear(){
        $person = Person::newFromId($this->reportItem->personId);
        $fecInfo = $person->getFecPersonalInfo();
        $phd_year_array = explode("-", $fecInfo->dateOfAppointment);
        return $phd_year_array[0];
    }
}

?>
