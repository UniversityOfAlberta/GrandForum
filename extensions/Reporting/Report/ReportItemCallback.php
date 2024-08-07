<?php

class ReportItemCallback {
    
    static $reportCallback;
    
    static function call($reportItem, $func, $args=null){
        self::$reportCallback->reportItem = $reportItem;
        if($args == null){
            return call_user_func(array(self::$reportCallback, self::$callbacks[$func]));
        }
        else{
            return call_user_func_array(array(self::$reportCallback, self::$callbacks[$func]), $args);
        }
    }
    
    static $callbacks = 
        array(
            // Dates
            "2_years_ago" => "get2YearsAgo",
            "last_year" => "getLastYear",
            "this_year" => "getThisYear",
            "next_year" => "getNextYear",
            "next_year2" => "getNextYear2",
            "startDate" => "getStartDate",
            "endDate" => "getEndDate",
            // Projects
            "project_id" => "getProjectId",
            "project_name" => "getProjectName",
            "project_full_name" => "getProjectFullName",
            "project_url" => "getProjectUrl",
            "project_website" => "getProjectWebsite",
            "project_status" => "getProjectStatus",
            "project_description" => "getProjectDescription",
            "project_theme" => "getProjectTheme",
            "project_start" => "getProjectStart",
            "project_end" => "getProjectEnd",
            "project_length" => "getProjectLength",
            "project_leaders" => "getProjectLeaders",
            "project_leader_names" => "getProjectLeaderNames",
            "project_leader_ids" => "getProjectLeaderIds",
            "project_past_leader_names" => "getPastProjectLeaderNames",
            "project_nis" => "getProjectNIs",
            "project_evolved_from" => "getProjectEvolvedFrom",
            "project_evolved_into" => "getProjectEvolvedInto",
            "project_n_collaborators" => "getNCollaborators",
            "getProjectMilestones" => "getProjectMilestones",
            "getNStakeholders" => "getNStakeholders",
            "getNStakeholderProducts" => "getNStakeholderProducts",
            "project_n_partners" => "getNPartners",
            "project_n_products_other" => "getNProductsWithOther",
            "project_n_hqp_lead_author" => "getNHQPLeadAuthor",
            "project_n_hqp_co_author" => "getNHQPCoAuthor",
            "project_n_hqp_co_presenter" => "getNHQPCoPresenter",
            "project_n_hqp_interns" => "getNProductsWithHQPInterns",
            "project_contributions" => "getProjectContributions",
            "project_contributions_cash" => "getProjectContributionsCash",
            "project_contributions_inkind" => "getProjectContributionsInKind",
            "project_n_connected_projects" => "getNConnectedProjects",
            "project_n_hqp" => "getNHQP",
            "project_n_epic" => "getNEpic",
            "project_n_movedon" => "getNMovedOn",
            "project_n_progressed" => "getNProgressed",
            "project_intcomp_application" => "getIntCompApplication", // hard-coded strings
            // Milestones
            "milestone_id" => "getMilestoneId",
            "milestone_title" => "getMilestoneTitle",
            "milestone_oldtitle" => "getMilestoneOldTitle",
            "milestone_start_date" => "getMilestoneStartDate",
            "milestone_end_date" => "getMilestoneEndDate",
            "milestone_status" => "getMilestoneStatus",
            "milestone_changes" => "getMilestoneChanges",
            "milestone_description" => "getMilestoneDescription",
            "milestone_olddescription" => "getMilestoneOldDescription",
            "milestone_last_edited_by" => "getMilestoneLastEditedBy",
            // Reports
            "timestamp" => "getTimestamp",
            "time2date" => "time2date",
            "post_id" => "getPostId",
            "report_name" => "getReportName",
            "report_xmlname" => "getReportXMLName",
            "section_name" => "getSectionName",
            "report_sab_comments" => "getReportSABComments",
            "report_has_started" => "getReportHasStarted",
            // People
            "my_id" => "getMyId",
            "my_name" => "getMyName",
            "my_email" => "getMyEmail",
            "my_first_name" => "getMyFirstName",
            "my_last_name" => "getMyLastName",
            "my_roles" => "getMyRoles",
            "my_full_roles" => "getMyFullRoles",
            "parent_id" => "getParentId",
            "parent_name" => "getParentName",
            "parent_uni" => "getParentUni",
            "user_name" => "getUserName",
            "user_url" => "getUserUrl",
            "user_email" => "getUserEmail",
            "user_phone" => "getUserPhone",
            "user_gender" => "getUserGender",
            "user_twitter" => "getUserTwitter",
            "user_reversed_name" => "getUserReversedName",
            "user_stakeholder" => "getUserStakeholder",
            "user_last_name" => "getUserLastName",
            "user_first_name" => "getUserFirstName",
            "user_id" => "getUserId",
            "user_roles" => "getUserRoles",
            "user_full_roles" => "getUserFullRoles",
            "user_sub_roles" => "getUserSubRoles",
            "user_level" => "getUserLevel",
            "user_dept" => "getUserDept",
            "user_uni" => "getUserUni",
            "user_nationality" => "getUserNationality",
            "user_supervisors" => "getUserSupervisors",
            "user_supervisor_id" => "getUserSupervisorId",
            "user_projects" => "getUserProjects",
            "user_project_end_date" => "getUserProjectEndDate",
            "user_tvn_file_number" => "getTVNFileNumber", // hard-coded strings
            // Sub-PL (SPL)
            "spl_subprojects" => "getSPLSubProjects",
            // SAB
            "sab_strength" => "getSABStrength",
            "sab_weakness" => "getSABWeakness",
            "sab_ranking" => "getSABRanking",
            "sab_summary" => "getSABSummary",
            // RMC
            "rmc_project_rank" => "getRMCProjectRank",
            "rmc_project_confidence" => "getRMCProjectConfidence",
            "rmc_project_feedback" => "getRMCProjectFeedback",
            // HQP Application
            "hqp_application_uni" => "getHQPApplicationUni",
            "hqp_application_program" => "getHQPApplicationProgram", 
            // Products
            "product_id" => "getProductId",
            "product_title" => "getProductTitle",
            "product_url" => "getProductUrl",
            "product_authors" => "getProductAuthors",
            "getProductData" => "getProductData",
            // Contribution
            "contribution_id" => "getContributionId",
            "contribution_title" => "getContributionTitle",
            "contribution_url" => "getContributionUrl",
            "contribution_partners" => "getContributionPartners",
            "contribution_start" => "getContributionStart",
            "contribution_end" => "getContributionEnd",
            "contribution_cash" => "getContributionCash",
            "contribution_inkind" => "getContributionInkind",
            "contribution_total" => "getContributionTotal",
            // ELITE
            "getElitePostingField" => "getElitePostingField",
            // Other
            "wgUserId" => "getWgUserId",
            "wgServer" => "getWgServer",
            "wgScriptPath" => "getWgScriptPath",
            "GET" => "getGet",
            "networkName" => "getNetworkName",
            "id" => "getId",
            "name" => "getName",
            "index" => "getIndex",
            "value" => "getValue",
            "pdfHTML" => "getPDFHTML",
            "extraIndex" => "getExtraIndex",
            "getProgress" => "getProgress",
            "getDepartments" => "getDepartments",
            "getProjects" => "getProjects",
            "getProjectNames" => "getProjectNames",
            "getProjectTitles" => "getProjectTitles",
            "getNProducts" => "getNProducts",
            "getBlobMD5" => "getBlobMD5",
            "getBlobDate" => "getBlobDate",
            "getRawText" => "getRawText",
            "getText" => "getText",
            "getNumber" => "getNumber",
            "getHTML" => "getHTML",
            "getArray" => "getArray",
            "getExtra" => "getExtra",
            "getPDFUserId" => "getPDFUserId",
            "concat" => "concat",
            "trim" => "trim",
            "add" => "add",
            "subtract" => "subtract",
            "multiply" => "multiply",
            "divide" => "divide",
            "round" => "round",
            "number_format" => "number_format",
            "getArrayCount" => "getArrayCount",
            "isArrayComplete" => "isArrayComplete",
            "replace" => "replace",
            "substr" => "substr",
            "strtolower" => "strtolower",
            "strtoupper" => "strtoupper",
            "nl2br" => "nl2br",
            "comma" => "comma",
            "set" => "set",
            "get" => "get",
            "if" => "ifCond",
            "and" => "andCond",
            "or" => "orCond",
            "contains" => "contains",
            "!contains" => "notContains",
            "!" => "not",
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
    
    function getStartDate(){
        return $this->reportItem->getReport()->startDate;
    }
    
    function getEndDate(){
        return $this->reportItem->getReport()->endDate;
    }
    
    function getProjectId(){
        $project_id = 0;
        if($this->reportItem->projectId != 0){
            $project_id = $this->reportItem->projectId;
        }
        return $project_id;
    }
    
    function getProjectName(){
        $project_name = "";
        if($this->reportItem->projectId != 0){
            $project = Project::newFromHistoricId($this->reportItem->projectId);
            if($project == null){
                return "";
            }
            $project_name = $project->getName();
        }
        $project_name = str_replace("<", "&lt;", $project_name);
        $project_name = str_replace(">", "&gt;", $project_name);
        return $project_name;
    }
    
    function getProjectFullName(){
        $project_name = "";
        if($this->reportItem->projectId != 0){
            $project = Project::newFromHistoricId($this->reportItem->projectId);
            if($project == null){
                return "";
            }
            $project_name = $project->getFullName();
        }
        return $project_name;
    }
    
    function getProjectUrl(){
        $project_url = "";
        if($this->reportItem->projectId != 0){
            $project = Project::newFromHistoricId($this->reportItem->projectId);
            if($project == null){
                return "";
            }
            $project_url = $project->getUrl();
        }
        return $project_url;
    }
    
    function getProjectWebsite(){
        $website = "";
        if($this->reportItem->projectId != 0){
            $project = Project::newFromHistoricId($this->reportItem->projectId);
            $websiteUrl = $project->getWebsite();
            if($websiteUrl != "" && $websiteUrl != "http://" && $websiteUrl != "https://"){
                $website = "<a href='$websiteUrl' class='externalLink' target='_blank'>Website</a>";
            }
        }
        return $website;
    }
    
    function getProjectStatus(){
        $project_stat = "";
        if($this->reportItem->projectId != 0 ){
            $project = Project::newFromHistoricId($this->reportItem->projectId);
            if($project == null){
                return "";
            }
            $project_stat = $project->getStatus();
        }
        return $project_stat;
    }
    
    function getProjectDescription(){
        $project_desc = "";
        if($this->reportItem->projectId != 0 ){
            $project = Project::newFromHistoricId($this->reportItem->projectId);
            if($project == null){
                return "";
            }
            $project_desc = $project->getDescription();
        }
        return $project_desc;
    }
    
    function getProjectTheme(){
        $project_theme = "";
        if($this->reportItem->projectId != 0){
            $project = Project::newFromHistoricId($this->reportItem->projectId);
            if($project == null){
                return "";
            }
            $challenges = new Collection($project->getChallenges());
            $project_theme = implode(", ", $challenges->pluck('getAcronym()'));
        }
        return $project_theme;
    }
    
    function getProjectStart(){
        $project_start = "";
        if($this->reportItem->projectId != 0){
            $project = Project::newFromHistoricId($this->reportItem->projectId);
            if($project == null){
                return "";
            }
            $project_start = substr($project->getStartDate(),0,10);
        }
        return $project_start;
    }
    
    function getProjectEnd(){
        $project_end = "";
        if($this->reportItem->projectId != 0){
            $project = Project::newFromHistoricId($this->reportItem->projectId);
            if($project == null){
                return "";
            }
            $project_end = substr($project->getEndDate(),0,10);
        }
        return $project_end;
    }
    
    function getProjectLength(){
        if($this->reportItem->projectId != 0){
            $project = Project::newFromHistoricId($this->reportItem->projectId);
            if($project == null){
                return "";
            }
            $date1 = new DateTime($project->getStartDate());
            $date2 = new DateTime($project->getEndDate());
            $interval = $date1->diff($date2);
            $years = round(max(1, $interval->y+1));
            if($years == 0 || $years > 1){
                return "{$years} years";
            }
            else{
                return "{$years} year";
            }
        }
        return "0 years";
    }
    
    function getProjectLeaders(){
        $leads = array();
        if($this->reportItem->projectId != 0 ){
            $project = Project::newFromHistoricId($this->reportItem->projectId);
            if($project == null){
                return "";
            }
            $leaders = $project->getLeaders();
            foreach($leaders as $lead){
                $leads[$lead->getReversedName()] = "<a target='_blank' href='{$lead->getUrl()}'>{$lead->getNameForForms()}</a>";
            }
        }
        if(count($leads) == 0){
            $leads[] = "N/A";
        }
        return implode(", ", $leads);
    }
    
    function getProjectLeaderNames(){
        $leads = array();
        if($this->reportItem->projectId != 0 ){
            $project = Project::newFromHistoricId($this->reportItem->projectId);
            if($project == null){
                return "";
            }
            $leaders = $project->getLeaders();
            foreach($leaders as $lead){
                $leads[$lead->getReversedName()] = "{$lead->getNameForForms()}";
            }
        }
        if(count($leads) == 0){
            $leads[] = "N/A";
        }
        return implode(", ", $leads);
    }
    
    function getProjectLeaderIds($delim=", "){
        $leads = array();
        if($this->reportItem->projectId != 0 ){
            $project = Project::newFromHistoricId($this->reportItem->projectId);
            if($project == null){
                return "";
            }
            $leaders = $project->getLeaders();
            foreach($leaders as $lead){
                $leads[$lead->getReversedName()] = $lead->getId();
            }
        }
        return implode($delim, $leads);
    }
    
    function getPastProjectLeaderNames(){
        $leads = array();
        if($this->reportItem->projectId != 0 ){
            $project = Project::newFromHistoricId($this->reportItem->projectId);
            if($project == null){
                return "";
            }
            $leaders = $project->getAllPeopleDuring(PL, "0000-00-00", EOT);
            foreach($leaders as $lead){
                $leads[$lead->getReversedName()] = "{$lead->getNameForForms()}";
            }
        }
        if(count($leads) == 0){
            $leads[] = "N/A";
        }
        return implode(", ", $leads);
    }
    
    function getProjectNIs(){
        $nis = array();
        if($this->reportItem->projectId != 0){
            $project = Project::newFromHistoricId($this->reportItem->projectId);
            if($project == null){
                return "";
            }
            $year = $this->reportItem->getReport()->year;
            foreach($project->getAllPeopleDuring(null, $year."-04-01 00:00:00", ($year+1)."-03-31 23:59:59") as $ni){
                if(!$ni->isRole(PL, $project) && ($ni->isRoleDuring(NI, $year."-04-01 00:00:00", ($year+1)."-03-31 23:59:59"))){
                    $nis[] = "<a href='{$ni->getUrl()}' target='_blank'>{$ni->getNameForForms()}</a>";
                }
            }
        }
        if(count($nis) == 0){
            $nis[] = "N/A";
        }
        return implode(", ", $nis);
    }
    
    function getProjectEvolvedInto(){
        $projects = array();
        if($this->reportItem->projectId != 0 ){
            $project = Project::newFromHistoricId($this->reportItem->projectId);
            if($project == null){
                return "";
            }
            $succs = $project->getSuccs();
            $people = $project->getAllPeople();
            foreach($people as $key => $person){
                if(!$person->isRole(NI)){
                    unset($people[$key]);
                }
            }
            foreach($succs as $succ){
                $count = 0;
                foreach($succ->getAllPeople() as $person){
                    if(isset($people[$person->getId()]) && $person->isRole(NI)){
                        $count++;
                    }
                }
                $projects[$count][] = $succ->getName()."($count)";
            }
        }
        ksort($projects);
        $projects = array_reverse($projects);
        $newProjects = array();
        foreach($projects as $projs){
            foreach($projs as $proj){
                $newProjects[] = $proj;
            }
        }
        return implode(", ", $newProjects);
    }
    
    function getProjectEvolvedFrom(){
        $projects = array();
        if($this->reportItem->projectId != 0 ){
            $project = Project::newFromHistoricId($this->reportItem->projectId);
            if($project == null){
                return "";
            }
            $preds = $project->getPreds();
            $people = $project->getAllPeople();
            foreach($people as $key => $person){
                if(!$person->isRole(NI)){
                    unset($people[$key]);
                }
            }
            foreach($preds as $pred){
                $count = 0;
                foreach($pred->getAllPeople() as $person){
                    if(isset($people[$person->getId()]) && $person->isRole(NI)){
                        $count++;
                    }
                }
                $projects[$count][] = $pred->getName()."($count)";
            }
        }
        ksort($projects);
        $projects = array_reverse($projects);
        $newProjects = array();
        foreach($projects as $projs){
            foreach($projs as $proj){
                $newProjects[] = $proj;
            }
        }
        return implode(", ", $newProjects);
    }
    
    function getNFaceWithStakeholder($startDate = false, $endDate = false){
        $faces = array();
        if($this->reportItem->projectId != 0){
            $project = Project::newFromHistoricId($this->reportItem->projectId);
            if($project == null){
                return "";
            }
            $faceTmp = $project->getPapers('all', $startDate, $endDate);
            foreach($faceTmp as $face){
                if($face->getType() == "Project Meeting" || 
                   $face->getType() == "Workshop Presentation" ||
                   $face->getType() == "Seminar Presentation" ||
                   $face->getType() == "Industry Partner Meeting - In-Person" ||
                   $face->getType() == "Community Partner Meeting - In-Person" ||
                   $face->getType() == "Policy Partner Meeting- In-Person" ||
                   $face->getType() == "Older Adult and/or Caregiver Meeting - In-Person"){
                    foreach($face->getAuthors() as $author){
                        if($author->isStakeholder()){
                            $faces[] = $face;
                            break;
                        }
                    }
                }
            }
        }
        return count($faces);
    }
    
    function getNCollaborators($startDate = false, $endDate = false){
        $collaborators = array();
        if($this->reportItem->projectId != 0){
            $project = Project::newFromHistoricId($this->reportItem->projectId);
            if($project == null){
                return "";
            }
            $collaborators = $project->getAllPeopleDuring(CHAMP, $startDate, $endDate);
            foreach($project->getContributionsDuring($startDate, $endDate) as $c){
                foreach($c->getPartners() as $p){
                    $collaborators[$p->getOrganization()] = $p;
                }
            }
        }
        return count($collaborators);
    }
    
    function getProjectMilestones($status="", $endUser="", $delimiter="|"){
        $milestones = array();
        if($this->reportItem->projectId != 0){
            $project = Project::newFromHistoricId($this->reportItem->projectId);
            foreach($project->getMilestones() as $milestone){
                if(($status == "" || $status == $milestone->getStatus()) && // TODO: This might need to be changed to account for status change
                   ($endUser == "" || $endUser == $milestone->getEndUser())){ 
                    $milestones[] = str_replace($delimiter, "", "{$milestone->getActivity()->getName()} - {$milestone->getTitle()}");
                }
            }
        }
        return implode($delimiter, $milestones);
    }
    
    function getNStakeholders($startDate = false, $endDate = false, $stakeholderCategory="", $role = null){
        $stakeholders = array();
        $role = ($role == "" || $role == "null") ? null : $role;
        if($this->reportItem->projectId != 0){
            $project = Project::newFromHistoricId($this->reportItem->projectId);
            if($project == null){
                return "";
            }
            $people = $project->getAllPeopleDuring($role, $startDate, $endDate);
            foreach($people as $key => $person){
                if($person->isStakeHolder() && $person->getStakeholder() == $stakeholderCategory){
                    $stakeholders[$person->getId()] = $person;
                }
            }
        }
        return count($stakeholders);
    }
    
    function getNStakeholderProducts($startDate = false, $endDate = false, $stakeholderCategory="", $category="all", $type="all"){
        $products = array();
        if($this->reportItem->projectId != 0){
            $project = Project::newFromHistoricId($this->reportItem->projectId);
            if($project == null){
                return "";
            }
            $productTmp = $project->getPapers($category, $startDate, $endDate);
            foreach($productTmp as $product){
                foreach($product->getAuthors() as $author){
                    if($author->isStakeHolder() && $author->getStakeholder() == $stakeholderCategory){
                        $products[] = $product;
                        break;
                    }
                }
            }
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
        return count($products);
    }
    
    function getNPartners($startDate = false, $endDate = false, $industry = null, $level = null){
        $industry = ($industry == "" || $industry == "null") ? null : $industry;
        $level =    ($level    == "" || $level    == "null") ? null : $level;
        
        $collaborators = array();
        if($this->reportItem->projectId != 0){
            $project = Project::newFromHistoricId($this->reportItem->projectId);
            if($project == null){
                return "";
            }
            foreach($project->getContributionsDuring($startDate, $endDate) as $c){
                foreach($c->getPartners() as $p){
                    if(($industry == null || $p->getIndustry() == $industry) &&
                       ($level    == null || $p->getLevel()    == $level)){
                        $collaborators[$p->getOrganization()] = $p;
                    }
                }
            }
        }
        return count($collaborators);
    }
    
    function getNProductsWithOther($startDate = false, $endDate = false){
        $products = array();
        if($this->reportItem->projectId != 0){
            $project = Project::newFromHistoricId($this->reportItem->projectId);
            if($project == null){
                return "";
            }
            $productTmp = $project->getPapers('Scientific Excellence - Advancing Knowledge', $startDate, $endDate);
            foreach($productTmp as $product){
                foreach($product->getAuthors() as $author){
                    if($author->isRoleDuring(NI, $startDate, $endDate) && 
                       !$author->isMemberOfDuring($project, $startDate, $endDate) && 
                       count($author->getProjectsDuring($startDate, $endDate)) > 0){
                        $products[] = $product;
                        break;
                    }
                }
            }
        }
        return count($products); 
    }
    
    function getNHQPLeadAuthor($startDate = false, $endDate = false){
        $hqps = array();
        if($this->reportItem->projectId != 0){
            $project = Project::newFromHistoricId($this->reportItem->projectId);
            if($project == null){
                return "";
            }
            $productTmp = $project->getPapers('all', $startDate, $endDate);
            foreach($productTmp as $product){
                foreach($product->getAuthors() as $author){
                    if($author->isRoleDuring(HQP, $startDate, $endDate) && $author->isMemberOfDuring($project, $startDate, $endDate)){
                        $hqps[$author->getId()] = $author;
                    }
                    break; // Only do the first
                }
            }
        }
        return count($hqps);
    }
    
    function getNHQPCoAuthor($startDate = false, $endDate = false){
        $hqps = array();
        if($this->reportItem->projectId != 0){
            $project = Project::newFromHistoricId($this->reportItem->projectId);
            if($project == null){
                return "";
            }
            $productTmp = $project->getPapers('all', $startDate, $endDate);
            foreach($productTmp as $product){
                foreach($product->getAuthors() as $key => $author){
                    if($key > 0){
                        if($author->isRoleDuring(HQP, $startDate, $endDate) && $author->isMemberOfDuring($project, $startDate, $endDate)){
                            $hqps[$author->getId()] = $author;
                        }
                    }
                }
            }
        }
        return count($hqps);
    }
    
    function getNHQPCoPresenter($startDate = false, $endDate = false){
        $hqps = array();
        if($this->reportItem->projectId != 0){
            $project = Project::newFromHistoricId($this->reportItem->projectId);
            if($project == null){
                return "";
            }
            $productTmp = $project->getPapers('all', $startDate, $endDate);
            foreach($productTmp as $product){
                if(strstr($product->getType(), 'Presentation') !== false){
                    foreach($product->getAuthors() as $key => $author){
                        if($author->isRoleDuring(HQP, $startDate, $endDate) && $author->isMemberOfDuring($project, $startDate, $endDate)){
                            $hqps[$author->getId()] = $author;
                        }
                    }
                }
            }
        }
        return count($hqps);
    }
    
    function getNProductsWithHQPInterns($startDate = false, $endDate = false){
        $products = array();
        if($this->reportItem->projectId != 0){
            $project = Project::newFromHistoricId($this->reportItem->projectId);
            if($project == null){
                return "";
            }
            $productTmp = $project->getPapers('all', $startDate, $endDate);
            foreach($productTmp as $product){
                if($product->getType() == "Internship"){
                    foreach($product->getAuthors() as $key => $author){
                        if($author->isRoleDuring(HQP, $startDate, $endDate) && $author->isMemberOfDuring($project, $startDate, $endDate)){
                            $products[] = $product;
                            break;
                        }
                    }
                }
            }
        }
        return count($products);
    }
    
    function getProjectContributions($startDate = false, $endDate = false){
        $contributions = 0;
        if($this->reportItem->projectId != 0){
            $project = Project::newFromHistoricId($this->reportItem->projectId);
            if($project == null){
                return "";
            }
            foreach($project->getContributionsDuring($startDate, $endDate) as $contribution){
                $contributions += $contribution->getTotal();
            }
        }
        return number_format($contributions);
    }
    
    function getProjectContributionsCash($startDate = false, $endDate = false){
        $contributions = 0;
        if($this->reportItem->projectId != 0){
            $project = Project::newFromHistoricId($this->reportItem->projectId);
            foreach($project->getContributionsDuring($startDate, $endDate) as $contribution){
                $contributions += $contribution->getCash();
            }
        }
        return number_format($contributions);
    }
    
    function getProjectContributionsInKind($startDate = false, $endDate = false){
        $contributions = 0;
        if($this->reportItem->projectId != 0){
            $project = Project::newFromHistoricId($this->reportItem->projectId);
            if($project == null){
                return "";
            }
            foreach($project->getContributionsDuring($startDate, $endDate) as $contribution){
                $contributions += $contribution->getKind();
            }
        }
        return number_format($contributions);
    }
    
    function getNConnectedProjects($startDate = false, $endDate = false){
        $connectedProjects = array();
        if($this->reportItem->projectId != 0){
            $project = Project::newFromHistoricId($this->reportItem->projectId);
            if($project == null){
                return "";
            }
            foreach($project->getPapers("all", $startDate, $endDate) as $product){
                foreach($product->getProjects() as $proj){
                    if($proj->getId() != $project->getId()){
                        $connectedProjects[$proj->getId()] = $proj;
                    }
                }
            }
        }
        return count($connectedProjects);
    }
    
    function getNProducts($startDate = false, $endDate = false, $category="all", $type="all", $data=""){
        $products = array();
        if($this->reportItem->projectId != 0){
            // Project Products
            $project = Project::newFromHistoricId($this->reportItem->projectId);
            if($project == null){
                return "";
            }
            $products = $project->getPapers($category, $startDate, $endDate);
        }
        else if($this->reportItem->personId != 0){
            // Person Products
            $person = Person::newFromId($this->reportItem->personId);
            $products = $person->getPapersAuthored($category, $startDate, $endDate, true, true);
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
            $ds = explode("|", $data);
            foreach($ds as $d){
                $datas = explode("=", $d);
                foreach($products as $key => $product){
                    $productData = $product->getData();
                    if($datas[0] == "status"){
                        if($product->getStatus() != $datas[1]){
                            unset($products[$key]);
                        }
                    }
                    else if(!isset($productData[$datas[0]]) || $productData[$datas[0]] != $datas[1]){
                        // Data doesn't match
                        unset($products[$key]);
                    }
                }
            }
        }
        return count($products);
    }
    
    function getNHQP($startDate = false, $endDate = false){
        if($this->reportItem->projectId != 0){
            $project = Project::newFromHistoricId($this->reportItem->projectId);
            return count($project->getAllPeopleDuring(HQP, $startDate, $endDate));
        }
        return 0;
    }
    
    function getNEpic($startDate = false, $endDate = false){
        $epics = array();
        if($this->reportItem->projectId != 0){
            $project = Project::newFromHistoricId($this->reportItem->projectId);
            if($project == null){
                return "";
            }
            $people = $project->getAllPeopleDuring(HQP, $startDate, $endDate);
            foreach($people as $person){
                if($person->isEPIC()){
                    $epics[] = $person;
                }
            }
        }
        return count($epics);
    }
    
    function getNMovedOn($startDate = false, $endDate = false){
        $movedOns = array();
        if($this->reportItem->projectId != 0){
            $project = Project::newFromHistoricId($this->reportItem->projectId);
            if($project == null){
                return "";
            }
            $people = $project->getAllPeopleDuring(HQP, $startDate, $endDate);
            foreach($people as $person){
                $movedOn = $person->getAllMovedOn($startDate, $endDate);
                if(count($movedOn) > 0){
                    $movedOns[] = $person;
                }
            }
        }
        return count($movedOns);
    }
    
    function getNProgressed($startDate = false, $endDate = false){
        $progressed = array();
        if($this->reportItem->projectId != 0){
            $project = Project::newFromHistoricId($this->reportItem->projectId);
            if($project == null){
                return "";
            }
            $people = $project->getAllPeopleDuring(HQP, $startDate, $endDate);
            foreach($people as $person){
                $positions = array();
                foreach($person->getUniversitiesDuring($startDate, $endDate) as $uni){
                    $position = strtolower($uni['position']);
                    if($position == "graduate student - doctoral" ||
                       $position == "graduate student - master's" ||
                       $position == "post-doctoral fellow" ||
                       $position == "undergraduate"){
                        $positions[$position] = $position;
                    }
                }
                if(count($positions) > 1){
                    $progressed[] = $person;
                }
            }
        }
        return count($progressed);
    }
    
    function getMilestoneId(){
        return $this->reportItem->milestoneId;
    }
    
    function getMilestoneTitle(){
        $milestone_title = "";
        if($this->reportItem->milestoneId != 0){
            $milestone = Milestone::newFromId($this->reportItem->milestoneId);
            $milestone_title = $milestone->getTitle();
        }
        return $milestone_title;
    }
    
    function getMilestoneOldTitle(){
        $milestone_title = "";
        if($this->reportItem->milestoneId != 0 ){
            $milestone = Milestone::newFromId($this->reportItem->milestoneId)->getRevisionByDate((REPORTING_YEAR-1)."-12-00");
            if($milestone != null){
                $milestone_title = $milestone->getTitle();
            }
        }
        return $milestone_title;
    }
    
    function getMilestoneStartDate(){
        $milestone_start = "";
        if($this->reportItem->milestoneId != 0 ){
            $milestone = Milestone::newFromId($this->reportItem->milestoneId);
            $milestone_start = new DateTime($milestone->getVeryStartDate());
            $milestone_start = date_format($milestone_start, 'F, Y');
        }
        return $milestone_start;
    }
    
    function getMilestoneEndDate(){
        $milestone_end = "";
        if($this->reportItem->milestoneId != 0 ){
            $milestone = Milestone::newFromId($this->reportItem->milestoneId);
            $milestone_end = new DateTime($milestone->getProjectedEndDate());
            $milestone_end = date_format($milestone_end, 'F, Y');
        }
        return $milestone_end;
    }
    
    function getMilestoneStatus(){
        $milestone_title = "";
        if($this->reportItem->milestoneId != 0 ){
            $milestone = Milestone::newFromId($this->reportItem->milestoneId);
            $milestone_title = $milestone->getStatus();
        }
        return $milestone_title;
    }
    
    function getMilestoneDescription(){
        $milestone_description = "";
        if($this->reportItem->milestoneId != 0 ){
            $milestone = Milestone::newFromId($this->reportItem->milestoneId);
            $milestone_description = $milestone->getDescription();
        }
        return $milestone_description;
    }
    
    function getMilestoneOldDescription(){
        $milestone_description = "";
        if($this->reportItem->milestoneId != 0 ){
            $milestone = Milestone::newFromId($this->reportItem->milestoneId)->getRevisionByDate((REPORTING_YEAR-1)."-12-00");
            if($milestone != null){
                $milestone_description = $milestone->getDescription();
            }
        }
        return $milestone_description;
    }
    
    function getMilestoneChanges(){
        $nChanges = 0;
        if($this->reportItem->milestoneId != 0 ){
            $currentMilestone = Milestone::newFromId($this->reportItem->milestoneId);
            $milestone = $currentMilestone->getRevisionByDate((REPORTING_YEAR-1)."-12-00");
            if($milestone == null){
                $first = $currentMilestone;
                $milestone = $first;
                while($first != null){
                    $milestone = $first;
                    $first = $first->getParent();
                }
            }
            $parent = $currentMilestone;
            while($parent != null && $parent->getId() != $milestone->getId()){
                $nChanges++;
                $parent = $parent->getParent();
            }
        }
        return $nChanges;
    }
    
    function getMilestoneLastEditedBy(){
        $edited = "";
        if($this->reportItem->milestoneId != 0 ){
            $milestone = Milestone::newFromId($this->reportItem->milestoneId);
            if($milestone->getEditedBy() != null && $milestone->getEditedBy()->getName() != ""){
                $edited = "<a href='{$milestone->getEditedBy()->getUrl()}'>{$milestone->getEditedBy()->getNameForForms()}</a>";
            }
        }
        return $edited;
    }
    
    function getReportSABComments(){
        $ret = "";
        $sabs = Person::getAllPeopleDuring(ISAC, $this->reportItem->getReport()->year.'-04-01', ($this->reportItem->getReport()->year+1).'-03-31');
        
        $index = 1;
        foreach($sabs as $sab){
            $strength = $this->getSABStrength($sab->getId());
            $weakness = $this->getSABWeakness($sab->getId());
            $ranking  = $this->getSABRanking($sab->getId());
            if($strength != "" || $weakness != ""){
                $ret .= "<h1>SAB Reviewer {$index}</h1>";
                $ret .= "<div style='margin-left:15px;'>";
                $ret .= "<h3>Project Strengths</h3>";
                $ret .= "<p>$strength</p>";
                $ret .= "<h3>Project Weaknesses</h3>";
                $ret .= "<p>$weakness</p>";
                $ret .= "<h3>Project Ranking</h3>";
                $ret .= "<p>$ranking</p>";
                $ret .= "</div>";
                $index++;
            }
        }
        $addr = ReportBlob::create_address(RP_SAB_REPORT, SAB_REPORT, SAB_REPORT_SUMMARY, 0);
        $blob = new ReportBlob(BLOB_TEXT, $this->reportItem->getReport()->year, 0, $this->reportItem->projectId);
        $blob->load($addr);
        $data = $blob->getData();
        if($data != ""){
            $ret .= "<h1>Summary</h1>";
            $ret .= "<div style='margin-left:15px;'>";
            $ret .= "<p>$data</p>";
            $ret .= "</div>";
        }
        return $ret;
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
    
    function getMyEmail(){
        $person = Person::newFromWgUser();
        return $person->getEmail();
    }
    
    function getMyFirstName(){
        $person = Person::newFromWgUser();
        return $person->getFirstName();
    }
    
    function getMyLastName(){
        $person = Person::newFromWgUser();
        return $person->getLastName();
    }
    
    function getMyRoles(){
        $person = Person::newFromWgUser();
        $project = Project::newFromHistoricId($this->reportItem->projectId);
        $roles = $person->getRoles();
        $roleNames = array();
        if(is_array($roles)){
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
        }
        return implode(", ", $roleNames);
    }
    
    function getMyFullRoles(){
        $person = Person::newFromWgUser();
        $project = Project::newFromHistoricId($this->reportItem->projectId);
        $roles = $this->getMyRoles();
        if($project != null && $project->getId() != 0){
            if($person->isRole(PL, $project)){
                if($roles != ""){
                    $roles .= ", ".PL;
                }
                else{
                    $roles .= PL;
                }
            }
        }
        else if($person->isRole(PL)){
            if($roles != ""){
                $roles .= ", ".PL;
            }
            else{
                $roles .= PL;
            }
        }
        return $roles;
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
    
    function getUserTwitter(){
        $person = Person::newFromId($this->reportItem->personId);
        return $person->getTwitter();
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
        return str_replace(")", "&#41;", str_replace("(", "&#40;", $person->getNameForForms()));
    }
    
    function getUserReversedName(){
        $person = Person::newFromId($this->reportItem->personId);
        return $person->getReversedName();
    }
    
    function getUserStakeholder(){
        $person = Person::newFromId($this->reportItem->personId);
        return $person->getStakeholder();
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
    
    function getUserRoles($start=null, $end=null){
        $person = Person::newFromId($this->reportItem->personId);
        $project = Project::newFromHistoricId($this->reportItem->projectId);
        if($start != null && $end != null){
            $roles = $person->getRolesDuring($start, $end);
        }
        else{
            $roles = $person->getRoles();
        }
        $roleNames = array();
        foreach($roles as $role){
            if($role->getRole() == PL){
                continue;
            }
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
    
    function getUserFullRoles($start=null, $end=null){
        $person = Person::newFromId($this->reportItem->personId);
        $project = Project::newFromHistoricId($this->reportItem->projectId);
        $roles = $this->getUserRoles($start, $end);
        if($project != null && $project->getId() != 0){
            if($person->isRole(PL, $project)){
                if($roles != ""){
                    $roles .= ", ".PL;
                }
                else{
                    $roles .= PL;
                }
            }
        }
        else if($person->isRole(PL)){
            if($roles != ""){
                $roles .= ", ".PL;
            }
            else{
                $roles .= PL;
            }
        }
        return $roles;
    }
    
    function getUserSubRoles(){
        global $config;
        $person = Person::newFromId($this->reportItem->personId);
        $roles = array();
        foreach(@$person->getSubRoles() as $subRole){
            $roles[] = $config->getValue('subRoles', $subRole);
        }
        return implode(", ", $roles);
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
                    if((strcmp($start, REPORTING_YEAR."-04-01 00:00:00") <= 0 && (strcmp($end, REPORTING_YEAR."-04-01 00:00:00") >= 0 || strcmp($end, "0000-00-00 00:00:00") == 0)) ||
                       (strcmp($start, (REPORTING_YEAR+1)."-03-31 23:59:59") <= 0 && strcmp($start, REPORTING_YEAR."-04-01 00:00:00") >= 0) ||
                       (strcmp($end, (REPORTING_YEAR+1)."-03-31 23:59:59") <= 0 && strcmp($end, REPORTING_YEAR."-04-01 00:00:00") >= 0)){
                        $sup = $rel->getUser1();
                        $supervisors[$sup->getId()] = "<a target='_blank' href='{$sup->getUrl()}'>{$sup->getNameForForms()}</a>";
                    }
                }
            }
        }
        if(count($supervisors) == 0){
            foreach(Person::getAllPeople('all') as $person){
                foreach($person->getRelations(SUPERVISES, true) as $rel){
                    if($rel->getUser2()->getId() == $me->getId()){
                        $sup = $rel->getUser1();
                        $supervisors[$sup->getId()] = "<a target='_blank' href='{$sup->getUrl()}'>{$sup->getNameForForms()}</a>";
                    }
                }
            }
        }
        return implode(", ", $supervisors);
    }
    
    function getUserSupervisorId(){
        $person = Person::newFromId($this->reportItem->personId);
        foreach($person->getSupervisors() as $supervisor){
            return $supervisor->getId();
        }
        return 0;
    }
    
    function getUserProjects(){
        $person = Person::newFromId($this->reportItem->personId);
        $projects = array();
        foreach($person->getProjectsDuring(REPORTING_YEAR."-04-01 00:00:00", (REPORTING_YEAR+1)."-03-31 23:59:59") as $project){
            if(!$project->isSubProject()){
                $deleted = ($project->isDeleted()) ? " (Ended)" : "";
                $projects[] = "<a target='_blank' href='{$project->getUrl()}'>{$project->getName()}{$deleted}</a>";
            }
        }
        if(count($projects) > 0){
            return implode(", ", $projects);
        }
        return "N/A";
    }
    
    function getDepartments($delim=", "){
        $departments = Person::getAllDepartments();
        sort($departments);
        return implode($delim, $departments);
    }
    
    function getProjects(){
        $person = Person::newFromId($this->reportItem->personId);
        $projects = array();
        foreach($person->getProjects() as $project){
            if(!$project->isSubProject()){
                $deleted = ($project->isDeleted()) ? " (Ended)" : "";
                $projects[] = "<a target='_blank' href='{$project->getUrl()}'>{$project->getName()}{$deleted}</a>";
            }
        }
        if(count($projects) > 0){
            return implode(", ", $projects);
        }
        return "N/A";
    }
    
    function getProjectNames($delim=", "){
        $projects = array();
        foreach(Project::getAllProjects() as $project){
            if(!$project->isSubProject()){
                $projects[] = "{$project->getName()}";
            }
        }
        if(count($projects) > 0){
            return implode($delim, $projects);
        }
        return "N/A";
    }
    
    function getProjectTitles($delim=", "){
        $projects = array();
        foreach(Project::getAllProjects() as $project){
            if(!$project->isSubProject()){
                $projects[] = "{$project->getFullName()}";
            }
        }
        if(count($projects) > 0){
            return implode($delim, $projects);
        }
        return "N/A";
    }
    
    function getUserProjectEndDate(){
        $person = Person::newFromId($this->reportItem->personId);
        $project = Project::newFromHistoricId($this->reportItem->projectId);
        $date = $project->getLeaveDate($person);
        if($date != "0000-00-00 00:00:00"){
            return time2date($project->getLeaveDate($person));
        }
        return "";
    }
    
    function getTVNFileNumber(){
        $id = $this->reportItem->personId;
        
        $fileNumbers = array(
            // IFP2016
            1       => "IFP2016-00",
            1791    => "IFP2016-01",
            1790    => "IFP2016-04",
            249     => "IFP2016-06",
            1789    => "IFP2016-07",
            1788    => "IFP2016-08",
            1787    => "IFP2016-09",
            1786    => "IFP2016-10",
            1785    => "IFP2016-11",
            1784    => "IFP2016-12",
            456     => "IFP2016-14",
            1783    => "IFP2016-16",
            285     => "IFP2016-18",
            1782    => "IFP2016-20",
            1781    => "IFP2016-21",
            1780    => "IFP2016-22",
            1761    => "IFP2016-23",
            1779    => "IFP2016-24",
            1778    => "IFP2016-25",
            1777    => "IFP2016-28",
            230     => "IFP2016-30",
            1776    => "IFP2016-31",
            1775    => "IFP2016-32",
            1774    => "IFP2016-33",
            1773    => "IFP2016-34",
            1772    => "IFP2016-35",
            // IFP2017
            2258    => "IFP2017-01",
            2260    => "IFP2017-02",
            2251    => "IFP2017-06",
            2269    => "IFP2017-10",
            2273    => "IFP2017-13",
            2225    => "IFP2017-15",
            2283    => "IFP2017-22",
            2290    => "IFP2017-27",
            2305    => "IFP2017-41",
            2309    => "IFP2017-45",
            // IFP2018
            389     => "IFP2018-03",
            2518    => "IFP2018-05",
            2541    => "IFP2018-06",
            2292    => "IFP2018-10",
            2511    => "IFP2018-11",
            2543    => "IFP2018-16",
            2536    => "IFP2018-17",
            2306    => "IFP2018-26",
            2551    => "IFP2018-31",
            2542    => "IFP2018-35",
            2790    => "IFP2018-40"
        );
        
        if(isset($fileNumbers[$id])){
            return $fileNumbers[$id];
        }
        return "";
    }
    
    function getIntCompApplication(){
        $project = Project::newFromId($this->reportItem->projectId);
        $map = array(
            "CSI-01"    => "27ac8f18f42477f21592c9fb2aaae253",
            "CSI-02"    => "6681227b7a285262e79fa17a1d06a3db",
            "CSI-04"    => "f3fb3a67184c13792571d4957740931b",
            "CSI-05"    => "0a399fb580b9309fbb4cd94c672b55bb",
            "CSI-06"    => "438b6d5cc0a1ac93326c27d19d61760d",
            "CSI-07"    => "67843e3200ca8c9d84c853e3714dab68",
            "CSI-08"    => "279538afe3c21746f46b17d6b56561b7",
            "CSI-09"    => "4941d83e8791520d22e7a02e25207a82",
            "IC LAB-01" => "63edac0f5e0344bf53537c57c7ed6bbc",
            "IC LAB-02" => "732a5b4c9d16fb7b0735d5658e02a170",
            "IC LAB-03" => "7e019c8c74023c8523f6b0596a4bda60",
            "IC LAB-04" => "721465d02208385d4719043442110eda",
            "IC LAB-05" => "7288edb4c5d46c3eef24b6a5241e0e4c",
            "IC LAB-06" => "4b4b4fdcc7afcc645552b31ce021e15c",
            "IC LAB-08" => "3364618939af7dc22562b9daa94b318a",
            "IC LAB-09" => "b84ce552ae9eb89f840f7296a60681ed",
            "IC LAB-10" => "7e30d9b81eee8c44fe699687cd324be4",
            "Ad-hoc-03" => "1293780dc83075a66896453cd71e345e",
            "Ad-hoc-04" => "13e14c67b9c07ff10385ff7a4b754e23",
            "Ad-hoc-05" => "e4b0428f09dd8ff7e2eaee26b8655f71",
            "Ad-hoc-06" => "",
            "Ad-hoc-07" => ""
        );
        if(isset($map[$project->getName()])){
            return $map[$project->getName()];
        }
        return "";
    }
    
    function getSPLSubProjects(){
        $person = Person::newFromId($this->reportItem->personId);
        $project = Project::newFromHistoricId($this->reportItem->projectId);
        
        $subs = array();
        foreach($project->getSubProjects() as $sub){
            if($person->isRole(PL, $sub)){
                $subs[] = "<a href='{$sub->getUrl()}' target='_blank'>{$sub->getName()}</a>";
            }
        }
        return implode(", ", $subs);
    }
    
    function getSABStrength($personId=-1){
        $personId = ($personId != -1) ? $personId : $this->reportItem->personId;
        $addr = ReportBlob::create_address(RP_SAB_REVIEW, SAB_REVIEW, SAB_REVIEW_STRENGTH, 0);
        $blb = new ReportBlob(BLOB_TEXT, $this->reportItem->getReport()->year, $personId, $this->reportItem->projectId);
        $result = $blb->load($addr);
        $data = $blb->getData();
        if($data != null){
           return $data;
        }
        return "";
    }
    
    function getSABWeakness($personId=-1){
        $personId = ($personId != -1) ? $personId : $this->reportItem->personId;
        $addr = ReportBlob::create_address(RP_SAB_REVIEW, SAB_REVIEW, SAB_REVIEW_WEAKNESS, 0);
        $blb = new ReportBlob(BLOB_TEXT, $this->reportItem->getReport()->year, $personId, $this->reportItem->projectId);
        $result = $blb->load($addr);
        $data = $blb->getData();
        if($data != null){
           return $data;
        }
        return "";
    }
    
    function getSABRanking($personId=-1){
        $personId = ($personId != -1) ? $personId : $this->reportItem->personId;
        $addr = ReportBlob::create_address(RP_SAB_REVIEW, SAB_REVIEW, SAB_REVIEW_RANKING, 0);
        $blb = new ReportBlob(BLOB_TEXT, $this->reportItem->getReport()->year, $personId, $this->reportItem->projectId);
        $result = $blb->load($addr);
        $data = $blb->getData();
        if($data != null){
           return $data;
        }
        return "";
    }
    
    function getSABSummary(){
        $addr = ReportBlob::create_address(RP_SAB_REPORT, SAB_REPORT, SAB_REPORT_SUMMARY, 0);
        $blb = new ReportBlob(BLOB_TEXT, $this->reportItem->getReport()->year, 0, $this->reportItem->projectId);
        $result = $blb->load($addr);
        $data = $blb->getData();
        if($data != null){
           return $data;
        }
        return "";
    }
    
    function getRMCProjectRank(){
        $addr = ReportBlob::create_address(RP_EVAL_PROJECT, RMC_REVIEW, EVL_OVERALLSCORE, 0);
        $blb = new ReportBlob(BLOB_TEXT, $this->reportItem->getReport()->year, $this->reportItem->personId, $this->reportItem->projectId);
        $result = $blb->load($addr);
        $data = $blb->getData();
        if($data != null){
            if(isset($data['original'])){
                return $data['original'];
            }
            else if(isset($data['revised'])){
                return $data['revised'];
            }
        }
        return "";
    }
    
    function getRMCProjectConfidence(){
        $addr = ReportBlob::create_address(RP_EVAL_PROJECT, RMC_REVIEW, EVL_CONFIDENCE, 0);
        $blb = new ReportBlob(BLOB_TEXT, $this->reportItem->getReport()->year, $this->reportItem->personId, $this->reportItem->projectId);
        $result = $blb->load($addr);
        $data = $blb->getData();
        if($data != null){
            if(isset($data['original'])){
                return $data['original'];
            }
            else if(isset($data['revised'])){
                return $data['revised'];
            }
        }
        return "";
    }
    
    function getRMCProjectFeedback(){
        $rp = RP_PROJ_REVIEW;
        $section = PROJ_REVIEW_FEEDBACK;
        $blobId = PROJ_FEEDBACK_COMM;
        $subId = "{$this->getUserId()}0{$this->getExtraIndex()}";
        $projectId = $this->getProjectId();
        $people = array_merge(Person::getAllPeople(RMC),
                              Person::getAllPeople(STAFF),
                              Person::getAllPeople(MANAGER),
                              Person::getAllPeople(ADMIN),
                              Person::getAllPeople(SD));
        $comments = array();
        foreach($people as $person){
            $personId = $person->getId();
            $comment = $this->getText($rp, $section, $blobId, $subId, $personId, $projectId);
            if($comment != ""){
                $comments[] = "<li>{$comment}</li>";
            }
        }
        return "<ul>".implode("\n", $comments)."</ul>";
    }
    
    function getHQPApplicationUni(){
        $addr = ReportBlob::create_address(RP_HQP_APPLICATION, HQP_APPLICATION_FORM, HQP_APPLICATION_UNI, 0);
        $blb = new ReportBlob(BLOB_TEXT, $this->reportItem->getReport()->year, $this->reportItem->personId, $this->reportItem->projectId);
        $result = $blb->load($addr);
        return $blb->getData();
    }
    
    function getHQPApplicationProgram(){
        $addr = ReportBlob::create_address(RP_HQP_APPLICATION, HQP_APPLICATION_FORM, HQP_APPLICATION_PROGRAM, 0);
        $blb = new ReportBlob(BLOB_TEXT, $this->reportItem->getReport()->year, $this->reportItem->personId, $this->reportItem->projectId);
        $result = $blb->load($addr);
        return $blb->getData();
    }
    
    function getProductId(){
        $product = Paper::newFromId($this->reportItem->productId);
        return $product->getId();
    }
    
    function getProductTitle(){
        $product = Paper::newFromId($this->reportItem->productId);
        return $product->getTitle();
    }
    
    function getProductUrl(){
        $product = Paper::newFromId($this->reportItem->productId);
        return $product->getUrl();
    }
    
    function getProductAuthors(){
        $product = Paper::newFromId($this->reportItem->productId);
        $names = array();
        foreach($product->getAuthors() as $author){
            $names[] = $author->getNameForProduct();
        }
        return implode(", ", $names);
    }
    
    function getProductData($field){
        $product = Paper::newFromId($this->reportItem->productId);
        return $product->getData($field);
    }
    
    function getContributionId(){
        $contribution = Contribution::newFromId($this->reportItem->productId);
        return $contribution->getId();
    }
            
    function getContributionTitle(){
        $contribution = Contribution::newFromId($this->reportItem->productId);
        return $contribution->getTitle();
    }
    
    function getContributionUrl(){
        $contribution = Contribution::newFromId($this->reportItem->productId);
        return $contribution->getUrl();
    }
    
    function getContributionPartners(){
        $contribution = Contribution::newFromId($this->reportItem->productId);
        $partners = array();
        foreach($contribution->getPartners() as $partner){
            $partners[] = $partner->getOrganization();
        }
        return implode(", ", $partners);
    }
    
    function getContributionStart(){
        $contribution = Contribution::newFromId($this->reportItem->productId);
        return substr($contribution->getStartDate(),0,10);
    }
    
    function getContributionEnd(){
        $contribution = Contribution::newFromId($this->reportItem->productId);
        return substr($contribution->getEndDate(),0,10);
    }
    
    function getContributionCash(){
        $contribution = Contribution::newFromId($this->reportItem->productId);
        return $contribution->getCash();
    }
    
    function getContributionInkind(){
        $contribution = Contribution::newFromId($this->reportItem->productId);
        return $contribution->getKind();
    }
    
    function getContributionTotal(){
        $contribution = Contribution::newFromId($this->reportItem->productId);
        return $contribution->getTotal();
    }
    
    function getElitePostingField($field){
        $elitePosting = ElitePosting::newFromId($this->reportItem->productId);
        return @$elitePosting->{$field};
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
            $project = Project::newFromHistoricId($projectId);
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
    
    function getPDFHTML(){
        return $this->reportItem->getText();
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
    
    function getProgress($sect=""){
        $report = $this->reportItem->getReport();
        foreach($report->sections as $section){
            if($section instanceof EditableReportSection){
                if($section->id == $sect){
                    return $section->getPercentComplete();
                }
            }
        }
        return 100;
    }
    
    function getBlobMD5($rp="", $section="", $blobId="", $subId="", $personId="", $projectId="", $year=null){
        if($rp == ""){
            return $this->reportItem->getMD5();
        }
        if($year == null){
            $year = $this->reportItem->getReport()->year;
        }
        $addr = ReportBlob::create_address($rp, $section, $blobId, $subId);
        $blb = new ReportBlob(BLOB_PDF, $year, $personId, $projectId);
        $result = $blb->load($addr, true);
        $md5 = $blb->getMD5();
        if($md5 != ""){
            return $md5;
        }
        $blb = new ReportBlob(BLOB_RAW, $year, $personId, $projectId);
        $result = $blb->load($addr, true);
        return $blb->getMD5();
    }
    
    function getBlobDate($rp="", $section="", $blobId="", $subId="", $personId="", $projectId="", $year=null){
        if($rp == ""){
            return $this->reportItem->getMD5();
        }
        if($year == null){
            $year = $this->reportItem->getReport()->year;
        }
        $addr = ReportBlob::create_address($rp, $section, $blobId, $subId);
        $blb = new ReportBlob(BLOB_RAW, $year, $personId, $projectId);
        $result = $blb->load($addr, true);
        return $blb->getLastChanged();
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
    
    function getRawText($rp, $section, $blobId, $subId, $personId, $projectId, $year=null){
        $year = ($year == null) ? $this->reportItem->getReport()->year : $year;
        $addr = ReportBlob::create_address($rp, $section, $blobId, $subId);
        $blb = new ReportBlob(BLOB_TEXT, $year, $personId, $projectId);
        $result = $blb->load($addr);
        return @str_replace(")", "&#41;", str_replace("(", "&#40;", $blb->getData()));
    }
    
    function getText($rp, $section, $blobId, $subId, $personId, $projectId, $year=null){
        $year = ($year == null) ? $this->reportItem->getReport()->year : $year;
        $addr = ReportBlob::create_address($rp, $section, $blobId, $subId);
        $blb = new ReportBlob(BLOB_TEXT, $year, $personId, $projectId);
        $result = $blb->load($addr);
        return @str_replace(")", "&#41;", str_replace("(", "&#40;", nl2br($blb->getData())));
    }
    
    function getNumber($rp, $section, $blobId, $subId, $personId, $projectId, $year=null){
        return (float) str_replace(",", "", $this->getText($rp, $section, $blobId, $subId, $personId, $projectId, $year));
    }
    
    function getArrayCount($rp, $section, $blobId, $subId, $personId, $projectId, $index=null){
        $array = $this->getArray($rp, $section, $blobId, $subId, $personId, $projectId, $index, "");
        return count($array);
    }
    
    function concat(){
        $args = func_get_args();
        $concat = "";
        foreach($args as $arg){
            $concat .= $arg;
        }
        return $concat;
    }
    
    function trim($str){
        return trim($str);
    }
    
    function add(){
        $args = func_get_args();
        $sum = 0;
        foreach($args as $arg){
            $sum += $arg;
        }
        return $sum;
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
        if($val == ""){
            return "";
        }
        return number_format(round($val, $dec), $dec, ".", "");
    }
    
    function number_format($val, $decimals=0, $dec_point="." , $thousands_sep=","){
        return (is_numeric($val)) ? number_format($val, $decimals, $dec_point, $thousands_sep) : $val;
    }
    
    function checkArray($data){
        $full = ($data != "");
        if(is_array($data)){
            foreach($data as $val){
                $full = ($full && $this->checkArray($val));
            }
        }
        return $full;
    }
    
    function isArrayComplete($rp, $section, $blobId, $subId, $personId, $projectId, $index=null){
        $data = $this->getArray($rp, $section, $blobId, $subId, $personId, $projectId);
        if($index != null && isset($data[$index])){
            $data = $data[$index];
        }
        $full = $this->checkArray($data);
        return $full;
    }
    
    function replace($pattern, $replacement, $string){
        return str_replace($pattern, $replacement, $string);
    }
    
    function substr($string, $start, $length){
        return substr($string, $start, $length);
    }
    
    function strtolower($str){
        return strtolower($str);
    }
    
    function strtoupper($str){
        return strtoupper($str);
    }
    
    function nl2br($str){
        return nl2br($str);
    }
    
    function comma(){
        return ",";
    }
    
    function set($key, $val){
        $this->reportItem->setVariable($key, $val);
    }
    
    function get($key){
        return $this->reportItem->getVariable($key);
    }
    
    function ifCond($condition, $result){
        $value = false;
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
    
    function not($val){
        return !$val;
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
    
    function getExtra($index=null){
        $set = $this->reportItem->extra;
        if($index == null){
            return $set;
        }
        if(isset($set[$index])){
            return $set[$index];
        }
        return "";
    }
    
    function getPDFUserId($tok){
        // This actually needs to be decrypted always, and sometimes twice
        $tok = decrypt($tok, true);
        $data = DBFunctions::execSQL("SELECT user_id
                                      FROM grand_pdf_report
                                      WHERE ((encrypted = 0 AND token = '".DBFunctions::escape($tok)."') OR 
                                             (encrypted = 1 AND token = '".DBFunctions::escape(decrypt($tok, true))."'))");
        if(!count($data) > 0){
            // PDF not found, check report blobs instead 
            $data = DBFunctions::execSQL("SELECT user_id
                                          FROM grand_report_blobs
                                          WHERE (md5 = '".DBFunctions::escape($tok)."')");
        }
        return @$data[0]['user_id'];
    }
    
    function getPostId(){
        return $this->reportItem->getPostId();
    }
    
    function getTimestamp($format="Y-m-d H:i:s T"){ 
        return date($format, time()); 
    }
    
    function time2date($time, $format='F j, Y'){
        return time2date($time, $format);
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
}

ReportItemCallback::$reportCallback = new ReportItemCallback(null);

?>
