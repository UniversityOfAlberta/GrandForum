<?php

class JobPostingAPI extends RESTAPI {
    
    function doGET(){
        $id = $this->getParam('id');
        $current = ($this->getParam('current') != "");
        if($id != ""){
            $job = JobPosting::newFromId($id);
            return $job->toJSON();
        }
        else{
            if($current){
                $jobs = new Collection(JobPosting::getCurrentJobPostings());
            }
            else {
                $jobs = new Collection(JobPosting::getAllJobPostings());
            }
            return $jobs->toJSON();
        }
        return $page->toJSON();
    }
    
    function validate(){
        if(trim($this->POST('jobTitle')) == ""){
            $this->throwError("A job title must be provided");
        }
        if(strlen($this->POST('jobTitle')) > 70){
            $this->throwError("The job title must be no longer than 70 characters");
        }
        if(($this->POST("deadlineType") == "Soft" || $this->POST("deadlineType") == "Hard") &&
           trim($this->POST("deadlineDate")) == ""){
            $this->throwError("A deadline must be provided");
        }
        if(trim($this->POST("deadlineDate")) != "" && date('Y-m-d') > $this->POST("deadlineDate")){
            $this->throwError("The deadline must be after todays date");
        }
        if(($this->POST("startDateType") == "No later than" || $this->POST("startDateType") == "No earlier than" || $this->POST("startDateType") == "Approximate") &&
           trim($this->POST("startDate")) == ""){
            $this->throwError("A start date must be provided");
        }
        if(trim($this->POST("startDate")) != "" && date('Y-m-d') > $this->POST("startDate")){
            $this->throwError("The start date must be after todays date");
        }
        if($this->POST("rank") == "Other" && trim($this->POST("rankOther")) == ""){
            $this->throwError("A rank must be provided");
        }
        if(strlen($this->POST('rankother')) > 30){
            $this->throwError("The rank must be no longer than 30 characters");
        }
        if(trim($this->POST("sourceLink")) == ""){
            $this->throwError("A source link must be provided");
        }
        if(strlen($this->POST('summary')) > 2000){
            $this->throwError("The summary must be no longer than 2000 characters");
        }
    }
    
    function doPOST(){
        $me = Person::newFromWgUser();
        if($me->isLoggedIn()){
            $this->validate();
            $job = new JobPosting(array());
            $keywords = $this->POST('keywords');
            $researchFields = $this->POST('researchFields');
            if(is_array($keywords)){
                $keywords = implode(", ", $this->POST('keywords'));
            }
            if(is_array($researchFields)){
                $researchFields = implode(", ", $this->POST('researchFields'));
            }
            $job->userId = $me->getId();
            $job->projectId = $this->POST('projectId');
            $job->visibility = $this->POST('visibility');
            $job->jobTitle = $this->POST('jobTitle');
            $job->deadlineType = $this->POST('deadlineType');
            $job->deadlineDate = $this->POST('deadlineDate');
            $job->startDateType = $this->POST('startDateType');
            $job->startDate = $this->POST('startDate');
            $job->tenure = $this->POST('tenure');
            $job->rank = $this->POST('rank');
            $job->rankOther = $this->POST('rankOther');
            $job->positionType = $this->POST('positionType');
            $job->researchFields = $researchFields;
            $job->keywords = $keywords;
            $job->contact = $this->POST('contact');
            $job->sourceLink = $this->POST('sourceLink');
            $job->summary = $this->POST('summary');
            $job->create();
            return $job->toJSON();
        }
        $this->throwError("You need to be logged in to create a Job Posting");
    }
    
    function doPUT(){
        $me = Person::newFromWgUser();
        $id = $this->getParam('id');
        if($me->isLoggedIn()){
            $job = JobPosting::newFromId($id);
            if($job->isAllowedToEdit()){
                $this->validate();
                $keywords = $this->POST('keywords');
                $researchFields = $this->POST('researchFields');
                if(is_array($keywords)){
                    $keywords = implode(", ", $this->POST('keywords'));
                }
                if(is_array($researchFields)){
                    $researchFields = implode(", ", $this->POST('researchFields'));
                }
                $job->projectId = $this->POST('projectId');
                $job->visibility = $this->POST('visibility');
                $job->jobTitle = $this->POST('jobTitle');
                $job->deadlineType = $this->POST('deadlineType');
                $job->deadlineDate = $this->POST('deadlineDate');
                $job->startDateType = $this->POST('startDateType');
                $job->startDate = $this->POST('startDate');
                $job->tenure = $this->POST('tenure');
                $job->rank = $this->POST('rank');
                $job->rankOther = $this->POST('rankOther');
                $job->positionType = $this->POST('positionType');
                $job->researchFields = $researchFields;
                $job->keywords = $keywords;
                $job->contact = $this->POST('contact');
                $job->sourceLink = $this->POST('sourceLink');
                $job->summary = $this->POST('summary');
                $job->update();
                return $job->toJSON();
            }
            else{
                $this->throwError("You are not allowed to edit this Job Posting");
            }
        }
        $this->throwError("You need to be logged in to create a Job Posting");
    }
    
    function doDELETE(){
        $me = Person::newFromWgUser();
        $id = $this->getParam('id');
        if($me->isLoggedIn()){
            $job = JobPosting::newFromId($id);
            if($job->isAllowedToEdit()){
                $job->delete();
                return $job->toJSON();
            }
            else{
                $this->throwError("You are not allowed to delete this Job Posting");
            }
        }
        $this->throwError("You need to be logged in to delete a Job Posting");
    }
	
}

?>
