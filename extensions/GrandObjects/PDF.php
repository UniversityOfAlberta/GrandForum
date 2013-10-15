<?php

class PDF extends BackboneModel {
    
    static $projectsCache = array();
    
    var $id;
    var $reportId;
    var $userId;
    var $generationUserId;
    var $submissionUserId;
    var $year;
    var $type;
    var $submitted;
    var $timestamp;
    var $project = false;
    
    /**
     * Returns a new PDF using the given report_id
     * @param int $id The report id of the pdf
     * @return PDF The PDF that matches the report_id
     */
    static function newFromId($id){
        $data = DBFunctions::select(array('grand_pdf_report'),
                                    array('report_id',
                                          'user_id',
                                          'generation_user_id',
                                          'submission_user_id',
                                          'year',
                                          'type',
                                          'submitted',
                                          'timestamp',
                                          'token'),
                                    array('report_id' => EQ($id)));
        return new PDF($data);
    }
    
    /**
     * Returns a new PDF using the given token
     * @param string $tok The token of the pdf
     * @return PDF The PDF that matches the token
     */
    static function newFromToken($tok){
        $data = DBFunctions::select(array('grand_pdf_report'),
                                    array('report_id',
                                          'user_id',
                                          'generation_user_id',
                                          'submission_user_id',
                                          'year',
                                          'type',
                                          'submitted',
                                          'timestamp',
                                          'token'),
                                    array('token' => EQ($tok)));
        return new PDF($data);
    }
    
    static function getAllPDFs(){
        $data = DBFunctions::select(array('grand_pdf_report'),
                                    array('report_id',
                                          'user_id',
                                          'generation_user_id',
                                          'submission_user_id',
                                          'year',
                                          'type',
                                          'submitted',
                                          'timestamp',
                                          'token'));
        $pdfs = array();
        foreach($data as $row){
            $pdfs[] = new PDF(array($row));
        }
        return $pdfs;
    }
    
    static function generateProjectsCache(){
        if(count(PDF::$projectsCache) == 0){
            $data = DBFunctions::select(array('grand_pdf_index'),
                                        array('report_id', 'sub_id'));
            foreach($data as $row){
                PDF::$projectsCache[$row['report_id']] = $row['sub_id'];
            }
        }
    }
    
    function PDF($data){
        if(count($data) > 0){
            $this->id = $data[0]['token'];
            $this->reportId = $data[0]['report_id'];
            $this->userId = $data[0]['user_id'];
            $this->generationUserId = $data[0]['generation_user_id'];
            $this->submissionUserId = $data[0]['submission_user_id'];
            $this->year = $data[0]['year'];
            $this->type = $data[0]['type'];
            $this->submitted = $data[0]['submitted'];
            $this->timestamp = $data[0]['timestamp'];
        }
    }
    
    function getId(){
        return $this->id;
    }
    
    function getReportId(){
        return $this->reportId;
    }
    
    function getPerson(){
        return Person::newFromId($this->userId);
    }
    
    function getGenerationPerson(){
        return Person::newFromId($this->generationUserId);
    }
    
    function getSubmissionPerson(){
        return Person::newFromId($this->submissionUserId);
    }
    
    function getSubmissionUserId(){
        return $this->submissionUserId;
    }
    
    function getYear(){
        return $this->year;
    }
    
    function getType(){
        return $this->type;
    }
    
    function getTimestamp(){
        return $this->timestamp;
    }
    
    function isSubmitted(){
        return $this->submitted;
    }
    
    function getUrl(){
        global $wgScriptPath, $wgServer;
        return "$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf={$this->id}";
    }
    
    function getProjectId(){
        PDF::generateProjectsCache();
        if(isset(PDF::$projectsCache[$this->getReportId()])){
            return PDF::$projectsCache[$this->getReportId()];
        }
        return "";
    }
    
    function getProject(){
        PDF::generateProjectsCache();
        if($this->project === false){
            if(isset(PDF::$projectsCache[$this->getReportId()])){
                $this->project = Project::newFromId(PDF::$projectsCache[$this->getReportId()]);
            }
            else{
                $this->project = null;
            }
        }
        return $this->project;
    }
    
    function getTitle(){
        $title = "";
        $report = AbstractReport::newFromToken($this->id);
        switch($this->type){
            default:
                if($report->project != null){
                    if($report->person->getId() == 0){
                        $title = "{$report->name}";
                    }
                    else{
                        $title = "{$report->person->getReversedName()} {$report->name}";
                    }
                }
                else{
                    $title = "{$report->person->getReversedName()} {$report->name}";
                }
                break;
        }
        return "{$this->year} {$title}";
    }
    
    /**
     * Returns the PDF data.  If the user is not allowed to read this PDF, then null is returned
     * @return string The PDF data, or null if the user is not allowed to read this PDF
     */
    function getPDF(){
        if($this->userCanRead()){
            $data = DBFunctions::select(array('mw_pdf_report'),
                                        array('pdf'),
                                        array('report_id' => $this->getReportId()));
            if(count($data) > 0){
                return $data[0]['pdf'];
            }
        }
        return null;
    }
    
    /**
     * Returns whether the current user can read the PDF or not
     * @return boolean Whether or not the current user can read this PDF
     */
    function canUserRead(){
        $me = Person::newFromWgUser();
        $start = $this->getYear().REPORTING_CYCLE_START_MONTH;
        $end = $this->getYear().REPORTING_CYCLE_END_MONTH;

        if(!$me->isLoggedIn()){
            // Not logged in?  Too bad, you can't read anything!
            return false;
        }
        else if($me->isRoleAtLeast(MANAGER)){
            // Managers should be able to see all pdfs
            return true;
        }
        else if($me->getId() == $this->userId ||
                $me->getId() == $this->generationUserId ||
                $me->getId() == $this->submissionUserId){
            // I should be able to read any pdf which was created by me
            return true;
        }
        else if($this->getType() == RPTP_HQP ||
                $this->getType() == RPTP_EXIT_HQP ||
                $this->getType() == RPTP_HQP_COMMENTS) {
            $hqps = $me->getHQPDuring($start, $end);
            foreach($hqps as $hqp){
                if($hqp->getId() == $this->userId){
                    // I should be able to read any pdf which was created by my hqp (for that year)
                    return true;
                }
            }
        }
        else if($this->getType() == RPTP_LEADER ||
                $this->getType() == RPTP_LEADER_COMMENTS ||
                $this->getType() == RPTP_LEADER_MILESTONES){
            if($this->getProjectId() != ""){
                $leads = $me->leadershipDuring($start, $end);
                foreach($leads as $project){
                    if($project->getId() == $this->getProjectId()){
                        // I should be able to read any pdf for a Project that I was a project leader to (for that year)
                        return true;
                    }
                }
            }
        }
        if($this->getType() == RPTP_LEADER ||
           $this->getType() == RPTP_NORMAL){
            if($me->isEvaluator($this->getYear())){
                $evals = $me->getEvaluateSubs($this->getYear());
                foreach($evals as $eval){
                    if($eval instanceof Project && 
                       $this->getType() == RPTP_LEADER){
                        if($this->getProjectId() == $eval->getId()){
                            // I should be able to read any pdf for the Projects that I am evaluating (for that year)
                            return true;
                        }
                    }
                    else if($eval instanceof Person &&
                            $this->getType() == RPTP_NORMAL){
                        if($this->getPerson()->getId() == $eval->getId()){
                            // I should be able to read any pdf for the People that I am evaluating (for that year)
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }
    
    function create(){
    
    }
    
    function update(){
    
    }
    
    function delete(){
    
    }
    
    function toArray(){
        return array('id' => $this->id,
                     'reportId' => $this->reportId,
                     'userId' => $this->userId,
                     'title' => $this->getTitle(),
                     'generationUserId' => $this->generationUserId,
                     'submissionUserId' => $this->submissionUserId,
                     'year' => $this->year,
                     'type' => $this->type,
                     'submitted' => $this->submitted,
                     'timestamp' => $this->timestamp,
                     'url' => $this->getUrl());
    }
    
    function exists(){
        return true;
    }
    
    function getCacheId(){
        
    }
    
}

?>
