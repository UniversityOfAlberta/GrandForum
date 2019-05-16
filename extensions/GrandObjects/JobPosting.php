<?php

/**
 * @package GrandObjects
 */

class JobPosting extends BackboneModel {
    
    var $id;
    var $userId;
    var $visibility;
    var $jobTitle;
    var $deadlineType;
    var $deadlineDate;
    var $startDateType;
    var $startDate;
    var $tenure;
    var $rank;
    var $rankOther;
    var $positionType;
    var $researchFields;
    var $keywords;
    var $contact;
    var $sourceLink;
    var $summary;
    var $created;
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
    
    static function getAllJobPostings(){
        $data = DBFunctions::select(array('grand_job_postings'),
                                    array('*'),
                                    array('deleted' => EQ(0)));
        $jobs = array();
        foreach($data as $row){
            $job = new JobPosting(array($row));
            if($job->isAllowedToView()){
                $jobs[] = $job;
            }
        }
        return $jobs;
    }
    
    function JobPosting($data){
        if(count($data) > 0){
            $row = $data[0];
            $this->id = $row['id'];
            $this->userId = $row['user_id'];
            $this->visibility = $row['visibility'];
            $this->jobTitle = $row['job_title'];
            $this->deadlineType = $row['deadline_type'];
            $this->deadlineDate = $row['deadline_date'];
            $this->startDateType = $row['start_date_type'];
            $this->startDate = $row['start_date'];
            $this->tenure = $row['tenure'];
            $this->rank = $row['rank'];
            $this->rankOther = $row['rank_other'];
            $this->positionType = $row['position_type'];
            $this->researchFields = $row['research_fields'];
            $this->keywords = $row['keywords'];
            $this->contact = $row['contact'];
            $this->sourceLink = $row['source_link'];
            $this->summary = $row['summary'];
            $this->created = $row['created'];
            $this->deleted = $row['deleted'];
        }
    }
    
    function getId(){
        return $this->id;
    }
    
    function getUserId(){
        return $this->userId;
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
    
    function getPositionType(){
        return $this->positionType;
    }
    
    function getResearchFields(){
        return $this->researchFields;
    }
    
    function getKeywords(){
        return $this->keywords;
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
    
    function getCreated(){
        return $this->created;
    }
    
    function isDeleted(){
        return $this->deleted;
    }
    
    /**
     * Returns the department name of this JobPosting's creator
     * @return string The department name of this JobPosting's creator
     */
    function getDepartment(){
        foreach($this->getUser()->getProjects() as $project){
            return $project->getFullName();
        }
        return "";
    }
    
    /**
     * Returns the university name of this JobPosting's creator
     * @return string The university name of this JobPosting's creator
     */
    function getUniversity(){
        foreach($this->getUser()->getProjects() as $project){
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
        if($me->getId() == $this->getUserId()){
            // Job was created by the logged in user
            return true;
        }
    }
    
    function toArray(){
        global $wgUser;
        $json = array('id' => $this->getId(),
                      'userId' => $this->getUserId(),
                      'visibility' => $this->getVisibility(),
                      'jobTitle' => $this->getJobTitle(),
                      'deadlineType' => $this->getDeadlineType(),
                      'deadlineDate' => $this->getDeadlineDate(),
                      'startDateType' => $this->getStartDateType(),
                      'startDate' => $this->getStartDate(),
                      'tenure' => $this->getTenure(),
                      'rank' => $this->getRank(),
                      'rankOther' => $this->getRankOther(),
                      'positionType' => $this->getPositionType(),
                      'researchFields' => $this->getResearchFields(),
                      'keywords' => $this->getKeywords(),
                      'contact' => $this->getContact(),
                      'sourceLink' => $this->getSourceLink(),
                      'summary' => $this->getSummary(),
                      'created' => $this->getCreated(),
                      'deleted' => $this->isDeleted(),
                      'department' => $this->getDepartment(),
                      'university' => $this->getUniversity(),
                      'isAllowedToEdit' => $this->isAllowedToEdit(),
                      'url' => $this->getUrl());
        return $json;
    }
    
    function create(){
        $me = Person::newFromWgUser();
        if($me->isLoggedIn()){
            $status = DBFunctions::insert('grand_job_postings',
                                          array('user_id' => $this->userId,
                                                'visibility' => $this->visibility,
                                                'job_title' => $this->jobTitle,
                                                'deadline_type' => $this->deadlineType,
                                                'deadline_date' => $this->deadlineDate,
                                                'start_date_type' => $this->startDateType,
                                                'start_date' => $this->startDate,
                                                'tenure' => $this->tenure,
                                                'rank' => $this->rank,
                                                'rank_other' => $this->rankOther,
                                                'position_type' => $this->positionType,
                                                'research_fields' => $this->researchFields,
                                                'keywords' => $this->keywords,
                                                'contact' => $this->contact,
                                                'source_link' => $this->sourceLink,
                                                'summary' => $this->summary));
            if($status){
                $this->id = DBFunctions::insertId();
            }
            return $status;
        }
        return false;
    }
    
    function update(){
        if($this->isAllowedToEdit()){
            $status = DBFunctions::update('grand_job_postings',
                                          array('user_id' => $this->userId,
                                                'visibility' => $this->visibility,
                                                'job_title' => $this->jobTitle,
                                                'deadline_type' => $this->deadlineType,
                                                'deadline_date' => $this->deadlineDate,
                                                'start_date_type' => $this->startDateType,
                                                'start_date' => $this->startDate,
                                                'tenure' => $this->tenure,
                                                'rank' => $this->rank,
                                                'rank_other' => $this->rankOther,
                                                'position_type' => $this->positionType,
                                                'research_fields' => $this->researchFields,
                                                'keywords' => $this->keywords,
                                                'contact' => $this->contact,
                                                'source_link' => $this->sourceLink,
                                                'summary' => $this->summary),
                                          array('id' => $this->id));
            return $status;
        }
        return false;
    }
    
    function delete(){
        if($this->isAllowedToEdit()){
            $status = DBFunctions::update('grand_job_postings',
                                array('deleted' => 1),
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
