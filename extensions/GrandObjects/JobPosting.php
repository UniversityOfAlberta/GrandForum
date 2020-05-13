<?php

/**
 * @package GrandObjects
 */

class JobPosting extends BackboneModel {
    
    var $id;
    var $userId;
    var $projectId;
    var $visibility;
    var $emailSent;
    var $jobTitle;
    var $jobTitleFr;
    var $deadlineType;
    var $deadlineDate;
    var $startDateType;
    var $startDate;
    var $tenure;
    var $rank;
    var $rankOther;
    var $language;
    var $positionType;
    var $researchFields;
    var $researchFieldsFr;
    var $keywords;
    var $keywordsFr;
    var $contact;
    var $sourceLink;
    var $summary;
    var $summaryFr;
    var $previewCode;
    var $created;
    var $modified;
    var $deleted;
    
    static function newFromId($id){
        $data = DBFunctions::select(array('grand_job_postings'),
                                    array('*'),
                                    array('id' => EQ($id)));
        $job = new JobPosting($data);
        if($job->isAllowedToView()){
            return $job;
        }
        else{
            return new JobPosting(array());
        }
    }
    
    /**
     * Returns an array of all Job Postings which this user is able to view
     */
    static function getAllJobPostings($incluedeDeleted=false){
        $where = ($incluedeDeleted) ? array() : array('deleted' => EQ(0));
        $data = DBFunctions::select(array('grand_job_postings'),
                                    array('*'),
                                    $where);
        $jobs = array();
        foreach($data as $row){
            $job = new JobPosting(array($row));
            if(isset($_GET['apiKey']) && $job->visibility != "Publish"){
                // Accessed using API Key, so restrict to Published only
                continue;
            }
            if($job->isAllowedToView()){
                $jobs[] = $job;
            }
        }
        return $jobs;
    }
    
    /**
     * Returns an array of Job Postings which have not yet expired
     */
    static function getCurrentJobPostings(){
        $newJobs = array();
        $jobs = self::getAllJobPostings();
        foreach($jobs as $job){
            if(isset($_GET['apiKey']) && $job->visibility != "Publish"){
                // Accessed using API Key, so restrict to Published only
                continue;
            }
            if($job->getDeadlineType() == "Open" || $job->getDeadlineDate() >= date('Y-m-d')){
                $newJobs[] = $job;
            }
        }
        return $newJobs;
    }
    
    /**
     * Returns an array of Postings that have been modified since the specified date
     */
    static function getNewPostings($date){
        $postings = static::getAllJobPostings(true);
        $return = array();
        foreach($postings as $posting){
            if($posting->modified >= $date){
                $return[] = $posting;
            }
        }
        return $return;
    }
    
    function JobPosting($data){
        if(count($data) > 0){
            $row = $data[0];
            $this->id = $row['id'];
            $this->userId = $row['user_id'];
            $this->projectId = $row['project_id'];
            $this->visibility = $row['visibility'];
            $this->emailSent = $row['email_sent'];
            $this->jobTitle = $row['job_title'];
            $this->jobTitleFr = $row['job_title_fr'];
            $this->deadlineType = $row['deadline_type'];
            $this->deadlineDate = $row['deadline_date'];
            $this->startDateType = $row['start_date_type'];
            $this->startDate = $row['start_date'];
            $this->tenure = $row['tenure'];
            $this->rank = $row['rank'];
            $this->rankOther = $row['rank_other'];
            $this->language = $row['language'];
            $this->positionType = $row['position_type'];
            $this->researchFields = $row['research_fields'];
            $this->researchFieldsFr = $row['research_fields_fr'];
            $this->keywords = $row['keywords'];
            $this->keywordsFr = $row['keywords_fr'];
            $this->contact = $row['contact'];
            $this->sourceLink = $row['source_link'];
            $this->summary = $row['summary'];
            $this->summaryFr = $row['summary_fr'];
            $this->previewCode = $row['preview_code'];
            $this->created = $row['created'];
            $this->modified = $row['modified'];
            $this->deleted = $row['deleted'];
        }
    }
    
    function getId(){
        return $this->id;
    }
    
    function getUserId(){
        return $this->userId;
    }
    
    function getProjectId(){
        return $this->projectId;
    }
    
    function getProject(){
        return Project::newFromId($this->projectId);
    }
    
    function getUser(){
        return Person::newFromId($this->getUserId());
    }
    
    function getVisibility(){
        return $this->visibility;
    }
    
    function getJobTitle(){
        return $this->jobTitle;
    }
    
    function getJobTitleFr(){
        return $this->jobTitleFr;
    }
    
    function getDeadlineType(){
        return $this->deadlineType;
    }
    
    function getDeadlineDate(){
        return substr($this->deadlineDate, 0, 10);
    }
    
    function getStartDateType(){
        return $this->startDateType;
    }
    
    function getStartDate(){
        return substr($this->startDate, 0, 10);
    }
    
    function getTenure(){
        return $this->tenure;
    }
    
    function getRank(){
        return $this->rank;
    }
    
    function getRankOther(){
        return $this->rankOther;
    }
    
    function getLanguage(){
        return $this->language;
    }
    
    function getPositionType(){
        return $this->positionType;
    }
    
    function getResearchFields(){
        return $this->researchFields;
    }
    
    function getResearchFieldsFr(){
        return $this->researchFieldsFr;
    }
    
    function getKeywords(){
        return $this->keywords;
    }
    
    function getKeywordsFr(){
        return $this->keywordsFr;
    }
    
    function getContact(){
        return $this->contact;
    }
    
    function getSourceLink(){
        return $this->sourceLink;
    }
    
    function getSummary(){
        return $this->summary;
    }
    
    function getSummaryFr(){
        return $this->summaryFr;
    }
    
    function getPreviewCode(){
        return $this->previewCode;
    }
    
    function getCreated(){
        return $this->created;
    }
    
    function getModified(){
        return $this->modified;
    }
    
    function isDeleted(){
        return $this->deleted;
    }
    
    function generatePreviewCode(){
        $this->previewCode = md5(microtime() + rand(0,1000));
        DBFunctions::update('grand_job_postings',
                            array('preview_code' => $this->previewCode),
                            array('id' => $this->id));
    }
    
    function sendEmail(){
        global $config, $wgServer, $wgScriptPath;
        if($wgScriptPath != ""){
            return;
        }
        if($this->getVisibility() == "Publish" && !$this->emailSent){
            // Always set content-type when sending HTML email
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

            // More headers
            $headers .= "From: {$config->getValue('supportEmail')}" . "\r\n";
            $rank = ($this->getRank() != "Other") ? $this->getRank() : $this->getRankOther();
            $message = "<p>There is a new job posting by {$this->getUniversity()} for {$rank} in the {$this->getDepartment()}</p>

                        <p>Details are available on the CS-Can | Info-Can website:</p>

		                <p><a href='{$this->getWebsiteUrl()}'>{$this->getWebsiteUrl()}</a></p>
		                
		                <p>This notification is sent by the <a href='{$wgServer}{$wgScriptPath}'>CS-Can | Info-Can Forum</a>.</p>";

            mail("heads@forum.cscan-infocan.ca","New job posting by {$this->getUniversity()}",$message,$headers);
            
            $this->emailSent = true;
            DBFunctions::update('grand_job_postings',
                                array('email_sent' => $this->emailSent),
                                array('id' => $this->id));
        }
    }
    
    /**
     * Returns the department name of this JobPosting's creator
     * @return string The department name of this JobPosting's creator
     */
    function getDepartment(){
        if($this->getProjectId() != 0){
            $project = $this->getProject();
            return $project->getFullName();
        }
        return "";
    }
    
    /**
     * Returns the university name of this JobPosting's creator
     * @return string The university name of this JobPosting's creator
     */
    function getUniversity(){
        if($this->getProjectId() != 0){
            $project = $this->getProject();
            return $project->getUniName();
        }
        return "";
    }
    
    /**
     * Returns the url of this JobPosting's page
     * @return string The url of this JobPosting's page
     */
    function getUrl(){
        global $wgServer, $wgScriptPath;
        if(!isset($_GET['embed']) || $_GET['embed'] == 'false'){
            return "{$wgServer}{$wgScriptPath}/index.php/Special:JobPostingPage#/{$this->getId()}";
        }
        return "{$wgServer}{$wgScriptPath}/index.php/Special:JobPostingPage?embed#/{$this->getId()}";
    }
    
    function getWebsiteUrl(){
        return "https://cscan-infocan.ca/careers/?job_id=".$this->getId();
    }
    
    function isAllowedToEdit(){
        $me = Person::newFromWgUser();
        return ($me->getId() == $this->getUserId() || $me->isRoleAtLeast(STAFF));
    }
    
    function isAllowedToView(){
        $me = Person::newFromWgUser();
        if($this->getVisibility() == "Publish"){
            // Job is Public
            return true;
        }
        if(($me->getId() == $this->getUserId() && !isset($_GET['apiKey'])) ||  
           ($me->isRoleAtLeast(STAFF) && $this->getPreviewCode() == @$_GET['previewCode']) ||
           ($me->isRoleAtLeast(STAFF) && !isset($_GET['apiKey']))){
            // Job was created by the logged in user
            return true;
        }
    }
    
    static function isAllowedToCreate(){
        $me = Person::newFromWgUser();
        return ($me->isLoggedIn() && ($me->isRoleAtLeast(MANAGER) || $me->isRole(PL) || $me->isRole(PA)));
    }
    
    function toArray(){
        global $wgUser;
        $project = null;
        $proj = $this->getProject();
        if($proj != null){
            $project = $proj->toArray();
        }
        $json = array('id' => $this->getId(),
                      'userId' => $this->getUserId(),
                      'user' => $this->getUser()->toArray(),
                      'projectId' => $this->getProjectId(),
                      'project' => $project,
                      'visibility' => $this->getVisibility(),
                      'jobTitle' => $this->getJobTitle(),
                      'jobTitleFr' => $this->getJobTitleFr(),
                      'deadlineType' => $this->getDeadlineType(),
                      'deadlineDate' => $this->getDeadlineDate(),
                      'startDateType' => $this->getStartDateType(),
                      'startDate' => $this->getStartDate(),
                      'tenure' => $this->getTenure(),
                      'rank' => $this->getRank(),
                      'rankOther' => $this->getRankOther(),
                      'language' => $this->getLanguage(),
                      'positionType' => $this->getPositionType(),
                      'researchFields' => $this->getResearchFields(),
                      'researchFieldsFr' => $this->getResearchFieldsFr(),
                      'keywords' => $this->getKeywords(),
                      'keywordsFr' => $this->getKeywordsFr(),
                      'contact' => $this->getContact(),
                      'sourceLink' => $this->getSourceLink(),
                      'summary' => $this->getSummary(),
                      'summaryFr' => $this->getSummaryFr(),
                      'previewCode' => $this->getPreviewCode(),
                      'created' => $this->getCreated(),
                      'modified' => $this->getModified(),
                      'deleted' => $this->isDeleted(),
                      'isAllowedToEdit' => $this->isAllowedToEdit(),
                      'url' => $this->getUrl());
        return $json;
    }
    
    function create(){
        if(self::isAllowedToCreate()){
            $status = DBFunctions::insert('grand_job_postings',
                                          array('user_id' => $this->userId,
                                                'project_id' => $this->projectId,
                                                'visibility' => $this->visibility,
                                                'job_title' => $this->jobTitle,
                                                'job_title_fr' => $this->jobTitleFr,
                                                'deadline_type' => $this->deadlineType,
                                                'deadline_date' => $this->deadlineDate,
                                                'start_date_type' => $this->startDateType,
                                                'start_date' => $this->startDate,
                                                'tenure' => $this->tenure,
                                                'rank' => $this->rank,
                                                'rank_other' => $this->rankOther,
                                                'language' => $this->language,
                                                'position_type' => $this->positionType,
                                                'research_fields' => $this->researchFields,
                                                'research_fields_fr' => $this->researchFieldsFr,
                                                'keywords' => $this->keywords,
                                                'keywords_fr' => $this->keywordsFr,
                                                'contact' => $this->contact,
                                                'source_link' => $this->sourceLink,
                                                'summary' => $this->summary,
                                                'summary_fr' => $this->summaryFr,
                                                'modified' => EQ(COL('CURRENT_TIMESTAMP'))));
            if($status){
                $this->id = DBFunctions::insertId();
                $this->generatePreviewCode();
                $this->sendEmail();
            }
            return $status;
        }
        return false;
    }
    
    function update(){
        if($this->isAllowedToEdit()){
            $status = DBFunctions::update('grand_job_postings',
                                          array('project_id' => $this->projectId,
                                                'visibility' => $this->visibility,
                                                'job_title' => $this->jobTitle,
                                                'job_title_fr' => $this->jobTitleFr,
                                                'deadline_type' => $this->deadlineType,
                                                'deadline_date' => $this->deadlineDate,
                                                'start_date_type' => $this->startDateType,
                                                'start_date' => $this->startDate,
                                                'tenure' => $this->tenure,
                                                'rank' => $this->rank,
                                                'rank_other' => $this->rankOther,
                                                'language' => $this->language,
                                                'position_type' => $this->positionType,
                                                'research_fields' => $this->researchFields,
                                                'research_fields_fr' => $this->researchFieldsFr,
                                                'keywords' => $this->keywords,
                                                'keywords_fr' => $this->keywordsFr,
                                                'contact' => $this->contact,
                                                'source_link' => $this->sourceLink,
                                                'summary' => $this->summary,
                                                'summary_fr' => $this->summaryFr,
                                                'modified' => EQ(COL('CURRENT_TIMESTAMP'))),
                                          array('id' => $this->id));
            $this->generatePreviewCode();
            $this->sendEmail();
            return $status;
        }
        return false;
    }
    
    function delete(){
        if($this->isAllowedToEdit()){
            $status = DBFunctions::update('grand_job_postings',
                                array('modified' => EQ(COL('CURRENT_TIMESTAMP')),
                                      'deleted' => 1),
                                array('id' => $this->id));
            if($status){
                $this->deleted = true;
            }
            return $status;
        }
        return false;
    }
    
    function exists(){
        return true;
    }
    
    function getCacheId(){
        global $wgSitename;
    }
}

?>
