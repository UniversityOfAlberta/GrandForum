<?php

/**
 * @package GrandObjects
 */

class PDF extends BackboneModel {
    
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
    var $projectId = 0;
    
    /**
     * Returns a new PDF using the given report_id
     * @param int $id The report id of the pdf
     * @return PDF The PDF that matches the report_id
     */
    static function newFromId($id){
        $id = mysql_real_escape_string($id);
        $data = DBFunctions::execSQL("SELECT r.report_id, r.user_id, r.generation_user_id, r.submission_user_id, r.year, r.type, r.submitted, r.timestamp, r.token, i.sub_id
                                      FROM `grand_pdf_report` r LEFT JOIN `grand_pdf_index` i ON r.report_id = i.report_id
                                      WHERE r.report_id = '{$id}'");
        return new PDF($data);
    }
    
    /**
     * Returns a new PDF using the given token
     * @param string $tok The token of the pdf
     * @return PDF The PDF that matches the token
     */
    static function newFromToken($tok){
        $tok = mysql_real_escape_string($tok);
        $data = DBFunctions::execSQL("SELECT r.report_id, r.user_id, r.generation_user_id, r.submission_user_id, r.year, r.type, r.submitted, r.timestamp, r.token, i.sub_id
                                      FROM `grand_pdf_report` r LEFT JOIN `grand_pdf_index` i ON r.report_id = i.report_id
                                      WHERE r.token = '{$tok}'");
        return new PDF($data);
    }
    
    static function getAllPDFs(){
        $data = DBFunctions::execSQL("SELECT r.report_id, r.user_id, r.generation_user_id, r.submission_user_id, r.year, r.type, r.submitted, r.timestamp, r.token, i.sub_id
                                      FROM (SELECT MAX(r1.report_id) as report_id
                                            FROM `grand_pdf_report` r1 LEFT JOIN `grand_pdf_index` i1 ON r1.report_id = i1.report_id
                                            GROUP BY r1.user_id, r1.year, r1.type, i1.sub_id) t1, `grand_pdf_report` r LEFT JOIN `grand_pdf_index` i ON r.report_id = i.report_id
                                      WHERE t1.report_id = r.report_id");
        $pdfs = array();
        foreach($data as $row){
            $pdfs[] = new PDF(array($row));
        }
        return $pdfs;
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
            $this->projectId = $data[0]['sub_id'];
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
        return $this->projectId;
    }
    
    function getProject(){
        if($this->project === false){
            if($this->projectId != null){
                $this->project = Project::newFromId($this->projectId);
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
        $result = false;
        wfRunHooks('CanUserReadPDF', array($me, $this, &$result));
        return $result;
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
