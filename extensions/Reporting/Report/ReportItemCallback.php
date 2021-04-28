<?php

class ReportItemCallback {
    
    static $callbacks = 
        array(
            // Dates
            "2_years_ago" => "get2YearsAgo",
            "last_year" => "getLastYear",
            "this_year" => "getThisYear",
            "next_year" => "getNextYear",
            "next_year2" => "getNextYear2",
            // Projects
            "project_id" => "getProjectId",
            "project_name" => "getProjectName",
            "project_full_name" => "getProjectFullName",
            "project_url" => "getProjectUrl",
            "project_website" => "getProjectWebsite",
            "project_status" => "getProjectStatus",
            "project_description" => "getProjectDescription",
            "project_theme" => "getProjectTheme",
            "project_leaders" => "getProjectLeaders",
            "project_problem" => "getProjectProblem",
            "project_solution" => "getProjectSolution",
            "project_nis" => "getProjectNIs",
            "project_champions" => "getProjectChampions",
            "project_evolved_from" => "getProjectEvolvedFrom",
            "project_evolved_into" => "getProjectEvolvedInto",
            "project_n_presentations" => "getNPresentations",
            "project_n_presentations_national" => "getNPresentationsNational",
            "project_n_presentations_international" => "getNPresentationsInternational",
            "project_n_products_with_partners" => "getNProductsWithPartners",
            "project_n_collaborators" => "getNCollaborators",
            "getNStakeholders" => "getNStakeholders",
            "getNStakeholderProducts" => "getNStakeholderProducts",
            "project_n_partners" => "getNPartners",
            "project_n_products_other" => "getNProductsWithOther",
            "project_n_products_hqp" => "getNProductsWithHQP",
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
            "milestone_assessment" => "getMilestoneAssessment",
            "milestone_oldassessment" => "getMilestoneOldAssessment",
            "milestone_last_edited_by" => "getMilestoneLastEditedBy",
            // Reports
            "timestamp" => "getTimestamp",
            "post_id" => "getPostId",
            "report_name" => "getReportName",
            "report_xmlname" => "getReportXMLName",
            "section_name" => "getSectionName",
            "report_sab_comments" => "getReportSABComments",
            "report_has_started" => "getReportHasStarted",
            // People
            "my_id" => "getMyId",
            "my_name" => "getMyName",
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
            "user_reversed_name" => "getUserReversedName",
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
            "user_projects" => "getUserProjects",
            "user_project_end_date" => "getUserProjectEndDate",
            "user_tvn_file_number" => "getTVNFileNumber", // hard-coded strings
            "user_requested_budget" => "getUserRequestedBudget",
            "user_allocated_budget" => "getUserAllocatedBudget",
            "user_subproject_comments" => "getUserSubProjectComments",
            "user_subproject_champs" => "getUserSubProjectChamps",
            "finalDecision" => "getFinalDecision",
            "finalProgram" => "getFinalProgram",
            "finalFunding" => "getFundingNote",
            "finalTime" => "getFullTimePartTime",
            // Champions
            "champ_org" => "getChampOrg",
            "champ_title" => "getChampTitle",
            "champ_subtitle" => "getChampSubTitle",
            "champ_subprojects" => "getChampSubProjects",
            "champ_full_project" => "getChampFullProject",
            "champ_is_still_champion" => "getChampIsStillChampion",
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
            //SOP
            "review_count" => "getReviewCount",
            "sop_table" => "getSopTable",
            // Other
            "wgUserId" => "getWgUserId",
            "wgServer" => "getWgServer",
            "wgScriptPath" => "getWgScriptPath",
            "networkName" => "getNetworkName",
            "id" => "getId",
            "name" => "getName",
            "index" => "getIndex",
            "value" => "getValue",
            "extraIndex" => "getExtraIndex",
            "getProjects" => "getProjects",
            "getNProducts" => "getNProducts",
            "getBlobMD5" => "getBlobMD5",
            "getString" => "getString",
            "getText" => "getText",
            "getNumber" => "getNumber",
            "getHTML" => "getHTML",
            "getArray" => "getArray",
            "getExtra" => "getExtra",
            "concat" =>"concat",
            "strlen" => "strlen",
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
    
    function getProjectId(){
        $project_id = $_GET['project'];//=0
        if($this->reportItem->projectId != 0){
            $project_id = $this->reportItem->projectId;
        }
        return $project_id;
    }
    
    function getProjectName(){
        $project_name = "";
        if($this->reportItem->projectId != 0 ){
            $project = Project::newFromId($this->reportItem->projectId);
            $project_name = $project->getName();
        }
        $project_name = str_replace("<", "&lt;", $project_name);
        $project_name = str_replace(">", "&gt;", $project_name);
        return $project_name;
    }
    
    function getProjectFullName(){
        $project_name = "";
        if($this->reportItem->projectId != 0 ){
            $project = Project::newFromId($this->reportItem->projectId);
            $project_name = $project->getFullName();
        }
        return $project_name;
    }
    
    function getProjectUrl(){
        $project_url = "";
        if($this->reportItem->projectId != 0 ){
            $project = Project::newFromId($this->reportItem->projectId);
            $project_url = $project->getUrl();
        }
        return $project_url;
    }
    
    function getProjectWebsite(){
        $website = "";
        if($this->reportItem->projectId != 0){
            $project = Project::newFromId($this->reportItem->projectId);
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
            $project = Project::newFromId($this->reportItem->projectId);
            $project_stat = $project->getStatus();
        }
        return $project_stat;
    }
    
    function getProjectDescription(){
        $project_desc = "";
        if($this->reportItem->projectId != 0 ){
            $project = Project::newFromId($this->reportItem->projectId);
            $project_desc = $project->getDescription();
        }
        return $project_desc;
    }
    
    function getProjectTheme(){
        $project_theme = "";
        if($this->reportItem->projectId != 0){
            $project = Project::newFromId($this->reportItem->projectId);
            $project_theme = $project->getChallenge()->getAcronym();
        }
        return $project_theme;
    }
    
    function getProjectLeaders(){
        $leads = array();
        if($this->reportItem->projectId != 0 ){
            $project = Project::newFromId($this->reportItem->projectId);
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
    
    function getProjectProblem(){
        $project_prob = "";
        if($this->reportItem->projectId != 0 ){
            $project = Project::newFromId($this->reportItem->projectId);
            $project_prob = $project->getProblem();
        }
        return $project_prob;
    }
    
    function getProjectSolution(){
        $project_sol = "";
        if($this->reportItem->projectId != 0 ){
            $project = Project::newFromId($this->reportItem->projectId);
            $project_sol = $project->getSolution();
        }
        return $project_sol;
    }
    
    function getProjectNIs(){
        $nis = array();
        if($this->reportItem->projectId != 0){
            $project = Project::newFromId($this->reportItem->projectId);
            $year = $this->reportItem->getReport()->year;
            foreach($project->getAllPeopleDuring(null, $year."-04-01 00:00:00", ($year+1)."-03-31 23:59:59") as $ni){
                if(!$ni->leadershipOf($project) && ($ni->isRoleDuring(NI, $year."-04-01 00:00:00", ($year+1)."-03-31 23:59:59"))){
                    $nis[] = "<a href='{$ni->getUrl()}' target='_blank'>{$ni->getNameForForms()}</a>";
                }
            }
        }
        if(count($nis) == 0){
            $nis[] = "N/A";
        }
        return implode(", ", $nis);
    }
    
    function getProjectChampions(){
        $champions = array();
        if($this->reportItem->projectId != 0 ){
            $project = Project::newFromId($this->reportItem->projectId);
            $champs = $project->getChampionsOn(($this->reportItem->getReport()->year+1).REPORTING_RMC_MEETING_MONTH);
            foreach($champs as $champ){
                $champions[] = "<a href='{$champ['user']->getUrl()}' target='_blank'>{$champ['user']->getNameForForms()}</a>";
            }
        }
        if(count($champions) == 0){
            $champions[] = "N/A";
        }
        return implode(", ", $champions);
    }
    
    function getProjectEvolvedInto(){
        $projects = array();
        if($this->reportItem->projectId != 0 ){
            $project = Project::newFromId($this->reportItem->projectId);
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
            $project = Project::newFromId($this->reportItem->projectId);
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
    
    function getNPresentations($startDate = false, $endDate = false){
        $presentations = array();
        if($this->reportItem->projectId != 0){
            $project = Project::newFromId($this->reportItem->projectId);
            $pres = $project->getPapers('Presentation', $startDate, $endDate);
            foreach($pres as $presentation){
                if($presentation->getStatus() == "Invited" || $presentation->getType() == "Invited Presentation"){
                    $presentations[] = $pres;
                }
            }
        }
        return count($presentations);
    }
    
    function getNPresentationsNational($startDate = false, $endDate = false){
        $presentations = array();
        if($this->reportItem->projectId != 0){
            $project = Project::newFromId($this->reportItem->projectId);
            $pres = $project->getPapers('Presentation', $startDate, $endDate);
            foreach($pres as $presentation){
                $data = $presentation->getData();
                if(@$data['level'] == "National" && 
                   ($presentation->getStatus() == "Invited" || $presentation->getType() == "Invited Presentation")){
                    $presentations[] = $pres;
                }
            }
        }
        return count($presentations);
    }
    
    function getNPresentationsInternational($startDate = false, $endDate = false){
        $presentations = array();
        if($this->reportItem->projectId != 0){
            $project = Project::newFromId($this->reportItem->projectId);
            $pres = $project->getPapers('Presentation', $startDate, $endDate);
            foreach($pres as $presentation){
                $data = $presentation->getData();
                if(@$data['level'] == "International" && 
                   ($presentation->getStatus() == "Invited" || $presentation->getType() == "Invited Presentation")){
                    $presentations[] = $pres;
                }
            }
        }
        return count($presentations);
    }
    
    function getNFaceWithStakeholder($startDate = false, $endDate = false){
        $faces = array();
        if($this->reportItem->projectId != 0){
            $project = Project::newFromId($this->reportItem->projectId);
            $faceTmp = $project->getPapers('all', $startDate, $endDate);
            foreach($faceTmp as $face){
                if($face->getType() == "Meeting" || 
                   $face->getType() == "Workshop Presentation" ||
                   $face->getType() == "Seminar Presentation"){
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
    
    function getNProductsWithPartners($startDate = false, $endDate = false){
        $products = array();
        if($this->reportItem->projectId != 0){
            $project = Project::newFromId($this->reportItem->projectId);
            $productTmp = $project->getPapers('all', $startDate, $endDate);
            foreach($productTmp as $product){
                if($product->getCategory() == "Publication" || $product->getCategory() == "Presentation"){
                    foreach($product->getAuthors() as $author){
                        if($author->isRoleDuring(CHAMP, $startDate, $endDate)){
                            $products[] = $product;
                            break;
                        }
                    }
                }
            }
        }
        return count($products);
    }
    
    function getNCollaborators($startDate = false, $endDate = false){
        $collaborators = array();
        if($this->reportItem->projectId != 0){
            $project = Project::newFromId($this->reportItem->projectId);
            $collaborators = $project->getAllPeopleDuring(CHAMP, $startDate, $endDate);
            foreach($project->getContributionsDuring($startDate, $endDate) as $c){
                foreach($c->getPartners() as $p){
                    $collaborators[$p->getOrganization()] = $p;
                }
            }
        }
        return count($collaborators);
    }
    
    function getNStakeholders($startDate = false, $endDate = false, $stakeholderCategory="", $role = null){
        $stakeholders = array();
        $role = ($role == "" || $role == "null") ? null : $role;
        if($this->reportItem->projectId != 0){
            $project = Project::newFromId($this->reportItem->projectId);
            $people = $project->getAllPeopleDuring($role, $startDate, $endDate);
            foreach($people as $key => $person){
                if($person->isStakeHolder() && $person->getStakeholder() == $stakeholderCategory){
                    $stakeholders[$person->getId()] = $person;
                }
            }
        }
        return count($stakeholders);
    }
    
    function getNStakeholderProducts($startDate = false, $endDate = false, $stakeholderCategory="", $category="all"){
        $products = array();
        if($this->reportItem->projectId != 0){
            $project = Project::newFromId($this->reportItem->projectId);
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
        return count($products);
    }
    
    function getNPartners($startDate = false, $endDate = false, $industry = null, $level = null){
        $industry = ($industry == "" || $industry == "null") ? null : $industry;
        $level =    ($level    == "" || $level    == "null") ? null : $level;
        
        $collaborators = array();
        if($this->reportItem->projectId != 0){
            $project = Project::newFromId($this->reportItem->projectId);
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
            $project = Project::newFromId($this->reportItem->projectId);
            $productTmp = $project->getPapers('Publication', $startDate, $endDate);
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
    
    function getNProductsWithHQP($startDate = false, $endDate = false){
        $products = array();
        if($this->reportItem->projectId != 0){
            $project = Project::newFromId($this->reportItem->projectId);
            $productTmp = $project->getPapers('Publication', $startDate, $endDate);
            foreach($productTmp as $product){
                foreach($product->getAuthors() as $author){
                    if($author->isRoleDuring(HQP, $startDate, $endDate)){
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
            $project = Project::newFromId($this->reportItem->projectId);
            $productTmp = $project->getPapers('Publication', $startDate, $endDate);
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
            $project = Project::newFromId($this->reportItem->projectId);
            $productTmp = $project->getPapers('Publication', $startDate, $endDate);
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
            $project = Project::newFromId($this->reportItem->projectId);
            $productTmp = $project->getPapers('Presentation', $startDate, $endDate);
            foreach($productTmp as $product){
                foreach($product->getAuthors() as $key => $author){
                    if($author->isRoleDuring(HQP, $startDate, $endDate) && $author->isMemberOfDuring($project, $startDate, $endDate)){
                        $hqps[$author->getId()] = $author;
                    }
                }
            }
        }
        return count($hqps);
    }
    
    function getNProductsWithHQPInterns($startDate = false, $endDate = false){
        $products = array();
        if($this->reportItem->projectId != 0){
            $project = Project::newFromId($this->reportItem->projectId);
            $productTmp = $project->getPapers('Activity', $startDate, $endDate);
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
            $project = Project::newFromId($this->reportItem->projectId);
            foreach($project->getContributionsDuring($startDate, $endDate) as $contribution){
                $contributions += $contribution->getTotal();
            }
        }
        return number_format($contributions);
    }
    
    function getProjectContributionsCash($startDate = false, $endDate = false){
        $contributions = 0;
        if($this->reportItem->projectId != 0){
            $project = Project::newFromId($this->reportItem->projectId);
            foreach($project->getContributionsDuring($startDate, $endDate) as $contribution){
                $contributions += $contribution->getCash();
            }
        }
        return number_format($contributions);
    }
    
    function getProjectContributionsInKind($startDate = false, $endDate = false){
        $contributions = 0;
        if($this->reportItem->projectId != 0){
            $project = Project::newFromId($this->reportItem->projectId);
            foreach($project->getContributionsDuring($startDate, $endDate) as $contribution){
                $contributions += $contribution->getKind();
            }
        }
        return number_format($contributions);
    }
    
    function getNConnectedProjects($startDate = false, $endDate = false){
        $connectedProjects = array();
        if($this->reportItem->projectId != 0){
            $project = Project::newFromId($this->reportItem->projectId);
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
            $project = Project::newFromId($this->reportItem->projectId);
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
            foreach($products as $key => $product){
                $productData = $product->getData();
                $datas = explode("=", $data);
                if($productData[$datas[0]] != $datas[1]){
                    // Data doesn't match
                    unset($products[$key]);
                }
            }
        }
        return count($products);
    }
    
    function getNHQP($startDate = false, $endDate = false){
        if($this->reportItem->projectId != 0){
            $project = Project::newFromId($this->reportItem->projectId);
            return count($project->getAllPeopleDuring(HQP, $startDate, $endDate));
        }
        return 0;
    }
    
    function getNEpic($startDate = false, $endDate = false){
        $epics = array();
        if($this->reportItem->projectId != 0){
            $project = Project::newFromId($this->reportItem->projectId);
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
            $project = Project::newFromId($this->reportItem->projectId);
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
            $project = Project::newFromId($this->reportItem->projectId);
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
    
    function getMilestoneAssessment(){
        $milestone_assessment = "";
        if($this->reportItem->milestoneId != 0 ){
            $milestone = Milestone::newFromId($this->reportItem->milestoneId);
            $milestone_assessment = $milestone->getAssessment();
        }
        return $milestone_assessment;
    }
    
    function getMilestoneOldAssessment(){
        $milestone_assessment = "";
        if($this->reportItem->milestoneId != 0 ){
            $milestone = Milestone::newFromId($this->reportItem->milestoneId)->getRevisionByDate((REPORTING_YEAR-1)."-12-00");
            if($milestone != null){
                $milestone_assessment = $milestone->getAssessment();
            }
        }
        return $milestone_assessment;
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
        $project = Project::newFromId($this->reportItem->projectId);
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
        $project = Project::newFromId($this->reportItem->projectId);
        $roles = $this->getMyRoles();
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
        if(count($roles) > 0){
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
        if($this->getNetworkName() == "GARS" || $this->getNetworkName() == "CSGARS"){
            return implode("; ", $roleNames);
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
                    $roles .= "&#44; PL";
                }
                else{
                    $roles .= "PL";
                }
            }
        }
        else if($person->isProjectLeader()){
            if($roles != ""){
                $roles .= "&#44; PL";
            }
            else{
                $roles .= "PL";
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
    
    function getUserProjectEndDate(){
        $person = Person::newFromId($this->reportItem->personId);
        $project = Project::newFromId($this->reportItem->projectId);
        $date = $project->getEndDate($person);
        if($date != "0000-00-00 00:00:00"){
            return time2date($project->getEndDate($person));
        }
        return "";
    }
    
    function getTVNFileNumber(){
        $id = $this->reportItem->personId;
        
        $fileNumbers = array(
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
            1772    => "IFP2016-35"
        );
        
        if(isset($fileNumbers[$id])){
            return $fileNumbers[$id];
        }
        return "";
    }
    
    function getUserRequestedBudget(){
        $person = Person::newFromId($this->reportItem->personId);
        $project = Project::newFromId($this->reportItem->projectId);
        
        $budget = $person->getRequestedBudget(REPORTING_YEAR);
        if($project != null && $project->getName() != ""){
            $budgetFirstCol = $budget->copy()->limitCols(0, 1);
            $budget = $budget->copy()->select(V_PROJ, array($project->getName()));
            $budget = $budgetFirstCol->join($budget);
            $budget->xls[0][1]->value = $person->getReversedName();
        }
        return $budget->render();
    }
    
    function getUserAllocatedBudget(){
        $person = Person::newFromId($this->reportItem->personId);
        $project = Project::newFromId($this->reportItem->projectId);
        
        $budget = $person->getAllocatedBudget(REPORTING_YEAR);
        if($project != null && $project->getName() != ""){
            $budgetFirstCol = $budget->copy()->limitCols(0, 1);
            $budget = $budget->copy()->select(V_PROJ, array($project->getName()));
            $budget = $budgetFirstCol->join($budget);
            $budget->xls[0][1]->value = $person->getReversedName();
        }
        return $budget->render();
    }
    
    function getUserSubProjectChamps(){
        $project = Project::newFromId($this->reportItem->projectId);
        
        $report = new DummyReport(RP_SUBPROJECT, new Person(array()), $project, $this->reportItem->getReport()->year);
        $item = $report->getSectionById("report")->getReportItemById("sub_project_champs");
        
        return $item->getHTMLForPDF();
    }

    function getFinalDecision() {
        $gsms = GsmsData::newFromId($this->getProjectId());
        $sop = $gsms->getSOP();
        return $sop->getFinalAdmit();
    }

    function getFinalProgram() {
        $gsms = GsmsData::newFromId($this->getProjectId());
        $prog = $gsms->admission_program_name;
        if (strpos($prog, "Master of Science (Thes)") !== false) {
            return "Master of Science (Thes)";
        } else if (strpos($prog, "Master of Science (Crse)") !== false) {
            return "Master of Science (Crse)";
        } else if (strpos($prog, "Doctor of Philosophy") !== false) {
            return "Doctor of Philosophy";
        }
        return $prog;
    }

    function getFundingNote() {
        $gsms = GsmsData::newFromId($this->getProjectId());
        $fund = $gsms->funding_note;
        if ($fund == "Y") {
            return "Yes";
        } else if ($fund == "N") {
            return "No";
        }
        return $fund;
    }

    function getFullTimePartTime() {
        $gsms = GsmsData::newFromId($this->getProjectId());
        $prog = $gsms->program;
        $progSplit = explode(" - ", $prog);
        $time = "";
        $time = @$progSplit[1];
        return $time;
    }
    
    function getUserSubProjectComments(){
        $project = Project::newFromId($this->reportItem->projectId);
        
        $report = new DummyReport(RP_SUBPROJECT, new Person(array()), $project, $this->reportItem->getReport()->year);
        $item = $report->getSectionById("report")->getReportItemById("sub_project_comments");
        
        return $item->getHTMLForPDF();
    }
    
    function getChampOrg(){
        return $this->getUserUni();
    }
    
    function getChampTitle(){
        return $this->getUserLevel();
    }
    
    function getChampSubTitle(){
        $person = Person::newFromId($this->reportItem->personId);
        $org = $person->getUni();
        $title = $person->getPosition();
        if($org != "" && $title != ""){
            return "$org, $title";
        }
        else if($org != "" && $title == ""){
            return $org;
        }
        else if($org == "" && $title != ""){
            return $title;
        }
        return "";
    }
    
    function getChampSubProjects(){
        $person = Person::newFromId($this->reportItem->personId);
        $project = Project::newFromId($this->reportItem->projectId);
        
        $subs = array();
        foreach($project->getSubProjects() as $sub){
            foreach($sub->getChampionsOn(($this->reportItem->getReport()->year+1).REPORTING_RMC_MEETING_MONTH) as $champ){
                if($champ['user']->getId() == $person->getId()){
                    $subs[] = "<a href='{$sub->getUrl()}' target='_blank'>{$sub->getName()}</a>";
                }
            }
        }
        return implode(", ", $subs);
    }
    
    function getChampFullProject(){
        $person = Person::newFromId($this->reportItem->personId);
        $project = Project::newFromId($this->reportItem->projectId);
        
        foreach($project->getSubProjects() as $sub){
            foreach($sub->getChampionsOn(($this->reportItem->getReport()->year+1).REPORTING_RMC_MEETING_MONTH) as $champ){
                if($champ['user']->getId() == $person->getId()){
                    return "";
                }
            }
        }
        return "Full Project";
    }
    
    function getChampIsStillChampion(){
        $person = Person::newFromId($this->reportItem->personId);
        $project = Project::newFromId($this->reportItem->projectId);
        
        $result = $person->isChampionOfOn($project, ($this->reportItem->getReport()->year+1).REPORTING_RMC_MEETING_MONTH.' 23:59:59');
        if(!$result && !$project->isSubProject()){
            foreach($project->getSubProjects() as $sub){
                $result = ($result || $person->isChampionOfOn($sub, ($this->reportItem->getReport()->year+1).REPORTING_RMC_MEETING_MONTH.' 23:59:59'));
            }
        }
        return (!$result) ? "style='color:red;text-decoration:line-through;'" : "";
    }
    
    function getSPLSubProjects(){
        $person = Person::newFromId($this->reportItem->personId);
        $project = Project::newFromId($this->reportItem->projectId);
        
        $subs = array();
        foreach($project->getSubProjects() as $sub){
            if($person->leadershipOf($sub)){
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
    
    function getBlobMD5($rp, $section, $blobId, $subId, $personId, $projectId, $year=null){
        if($year == null){
            $year = $this->reportItem->getReport()->year;
        }
        $addr = ReportBlob::create_address($rp, $section, $blobId, $subId);
        $blb = new ReportBlob(BLOB_PDF, $year, $personId, $projectId);
        $result = $blb->load($addr, true);
        return $blb->getMD5();
    }
    
    function getArray($rp, $section, $blobId, $subId, $personId, $projectId, $index=null, $delim=", "){
        $addr = ReportBlob::create_address($rp, $section, $blobId, $subId);
        $blb = new ReportBlob(BLOB_ARRAY, $this->reportItem->getReport()->year, $personId, $projectId);
        $result = $blb->load($addr);
        if($index == null){
            return $blb->getData();
        }
        else{
            $array = $blb->getData();
            $value = @$array[$index];
            if(is_array($value)){
                return @implode($delim, $value);
            }
            return $value;
        }
    }

    function getString($f) {
        return trim(file_get_contents("extensions/Reporting/Report/ReportStrings/$f"));
    }
    
    function getText($rp, $section, $blobId, $subId, $personId, $projectId){
        $addr = ReportBlob::create_address($rp, $section, $blobId, $subId);
        $blb = new ReportBlob(BLOB_TEXT, $this->reportItem->getReport()->year, $personId, $projectId);
        $result = $blb->load($addr);
        return str_replace(")", "&#41;", str_replace("(", "&#40;", nl2br($blb->getData())));
    }
    
    function getNumber($rp, $section, $blobId, $subId, $personId, $projectId){
        return (float) $this->getText($rp, $section, $blobId, $subId, $personId, $projectId);
    }
    
    function concat(){
        $args = func_get_args();
        $concat = "";
        foreach($args as $arg){
            $concat .= $arg;
        }
        return $concat;
    }
    
    function strlen($str){
        return strlen($str);
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
        return date("Y-m-d H:i:s T", time()); 
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

    function getReviewCount(){
        $gsms = GsmsData::newFromId($this->getProjectId());
        $sop = SOP::newFromUserId($gsms->user_id);
        $reviewers = $sop->getReviewers();
        return count($reviewers);
    }

    function getSopTable(){
        $sop = SOP::newFromId($this->getProjectId());
        $html = "
<table border=1>
  <tr>
    <th>Algorithm</th>
    <th>US Grade</th>
    <th>Minimum Age</th>
  </tr>
  <tr>
    <td>Readability Index</td>
    <td align='right'>{$sop->ari_grade}</td>
    <td align='right'>{$sop->ari_age}</td>
  </tr>
  <tr>
    <td>Coleman-Liau</td>
    <td align='right'>{$sop->colemanliau_grade}</td>
    <td align='right'>{$sop->colemanliau_age}</td>
  </tr>
  <tr>
    <td>Dalechall</td>
    <td align='right'>{$sop->dalechall_grade}</td>
    <td align='right'>{$sop->dalechall_age}</td>
  </tr>
  <tr>
    <td>Flesch-Kincaid</td>
    <td align='right'>{$sop->fleschkincaid_grade}</td>
    <td align='right'>{$sop->fleschkincaid_age}</td>
  </tr>
  <tr>
    <td>SMOG</td>
    <td align='right'>{$sop->smog_grade}</td>
    <td align='right'>{$sop->smog_age}</td>
  </tr>
</table>

<p>Flesch Reading Ease: {$sop->reading_ease}</p>
<p>Dalechall Readability Index: {$sop->dalechall_index}</p>
<p>Sentiment Type: {$sop->sentiment_type}</p>
<p>Sentiment Score: {$sop->sentiment_val}</p>
 <table border=1>
  <tbody>
  <tr>
    <th >Emotion Type</th>
    <th >Score</th>
  </tr>
  <tr>
    <td>Anger</td>
    <td align='right'>{$sop->anger_score}</td>
  </tr>
  <tr>
    <td>Disgust</td>
    <td align='right'>{$sop->disgust_score}</td>
  </tr>
  <tr>
    <td>Fear</td>
    <td align='right'>{$sop->fear_score}</td>
  </tr>
  <tr>
    <td>Joy</td>
    <td align='right'>{$sop->joy_score}</td>
  </tr>
</tbody>
</table>




";
        return $html;
    }

}

?>
